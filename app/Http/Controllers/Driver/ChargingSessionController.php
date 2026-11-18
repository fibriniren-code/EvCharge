<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Charger;
use App\Models\ChargingSession;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChargingSessionController extends Controller
{
    /** Saldo minimum agar boleh mengonfirmasi reservasi / memulai charging (Rp). */
    public const MIN_START_BALANCE = 20000;

    private function findUserSession($id): ChargingSession
    {
        return ChargingSession::where('user_id', auth()->id())->where('id_session', $id)->firstOrFail();
    }

    /** Pemeriksaan bersama sebelum charging boleh dimulai. Mengembalikan pesan error atau null. */
    private function startBlocker(Charger $charger): ?string
    {
        $user = auth()->user();

        if (! $charger->location?->isOpen()) {
            return 'Stasiun sedang ditutup sementara.';
        }

        return match ($charger->status) {
            'faulted' => 'Port charger sedang mengalami gangguan teknis.',
            'unavailable' => 'Port charger sedang dinonaktifkan / dalam pemeliharaan.',
            'occupied' => 'Port charger sedang digunakan pengemudi lain.',
            default => match (true) {
                $user->outstandingAmount() > 0 => 'Ada tagihan tertunggak. Top up saldo terlebih dahulu.',
                (float) $user->balance < self::MIN_START_BALANCE => 'Saldo minimal Rp ' . number_format(self::MIN_START_BALANCE, 0, ',', '.') . ' dibutuhkan untuk memulai charging.',
                ChargingSession::running()->where('user_id', $user->id)->exists() => 'Anda masih memiliki sesi charging yang berjalan.',
                default => null,
            },
        };
    }

    public function scan(Request $request)
    {
        Reservation::expireStale();

        $reservations = Reservation::with(['charger.location', 'vehicle'])
            ->where('user_id', auth()->id())
            ->holding()
            ->latest('id_reservation')
            ->get();

        return view('driver.charging.scan', [
            'reservations' => $reservations,
            'prefill' => $request->query('booking_id'),
        ]);
    }

    /** Verifikasi QR charger atau ID booking. */
    public function processScan(Request $request)
    {
        Reservation::expireStale();

        $request->validate([
            'qr_code' => 'nullable|string|max:255',
            'booking_id' => 'nullable|string|max:50',
        ]);

        if (! $request->filled('qr_code') && ! $request->filled('booking_id')) {
            return response()->json(['success' => false, 'message' => 'Scan QR Code atau masukkan ID Booking Anda.'], 422);
        }

        $userId = auth()->id();
        $statuses = ['pending', 'confirmed', 'active'];

        if ($request->filled('booking_id')) {
            $bookingId = (int) preg_replace('/\D/', '', $request->booking_id);

            $reservation = Reservation::with('charger.location')
                ->where('user_id', $userId)->where('id_reservation', $bookingId)->whereIn('status', $statuses)->first();

            if (! $reservation) {
                return response()->json(['success' => false, 'message' => 'ID Booking tidak ditemukan atau sudah tidak aktif.'], 404);
            }
        } else {
            // Ambil angka terakhir dari isi QR, mis. "CHG-VOLTRON-02" -> 2
            preg_match_all('/\d+/', (string) $request->qr_code, $m);
            $chargerId = (int) end($m[0]);

            $charger = $chargerId ? Charger::with('location')->find($chargerId) : null;

            if (! $charger) {
                return response()->json(['success' => false, 'message' => 'Mesin charger tidak ditemukan.'], 404);
            }

            $reservation = Reservation::with('charger.location')
                ->where('user_id', $userId)->where('charger_id', $charger->id_charger)->whereIn('status', $statuses)
                ->latest('id_reservation')->first();

            if (! $reservation) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki reservasi aktif pada charger ini.'], 400);
            }
        }

        $charger = $reservation->charger;

        // Sesi sudah berjalan: langsung arahkan ke monitoring.
        if ($reservation->status === 'active') {
            $running = ChargingSession::running()->where('id_reservation', $reservation->id_reservation)->first();
            if ($running) {
                return response()->json([
                    'success' => true,
                    'already_running' => true,
                    'redirect' => route('driver.charging.monitor', $running->id_session),
                ]);
            }
        }

        if ($reservation->status === 'pending') {
            return response()->json(['success' => false, 'message' => 'Konfirmasi reservasi Anda terlebih dahulu sebelum memulai charging.'], 422);
        }

        if ($error = $this->startBlocker($charger)) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        return response()->json([
            'success' => true,
            'reservation_id' => $reservation->id_reservation,
            'charger_label' => 'Charger #' . $charger->id_charger . ' · ' . $charger->charger_type,
            'location' => $charger->location?->nama_lokasi,
            'power_kw' => (float) $charger->power_kw,
        ]);
    }

    public function startSession(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id_reservation',
            'charging_type' => 'nullable|in:full,kwh,nominal',
        ]);

        $reservation = Reservation::where('user_id', auth()->id())->findOrFail($request->reservation_id);

        $session = DB::transaction(function () use ($reservation, &$error) {
            $reservation = Reservation::whereKey($reservation->id_reservation)->lockForUpdate()->first();
            $charger = Charger::with('location')->whereKey($reservation->charger_id)->lockForUpdate()->firstOrFail();

            if ($reservation->status !== 'confirmed') {
                $error = 'Reservasi belum dikonfirmasi atau sudah tidak aktif.';

                return null;
            }

            if ($error = $this->startBlocker($charger)) {
                return null;
            }

            $charger->update(['status' => 'occupied']);
            $reservation->update(['status' => 'active']);

            return ChargingSession::create([
                'user_id' => $reservation->user_id,
                'id_charger' => $reservation->charger_id,
                'id_reservation' => $reservation->id_reservation,
                'waktu_mulai' => now(),
                'energi_kwh' => 0,
                'estimasi_biaya' => 0,
                'status' => 'running',
            ]);
        });

        if (! $session) {
            return redirect()->route('driver.scan')->withErrors(['error' => $error ?? 'Charging tidak dapat dimulai.']);
        }

        return redirect()->route('driver.charging.monitor', $session->id_session)
            ->with('success', 'Pengisian daya dimulai. Selamat mengisi!');
    }

    public function monitorSession($id)
    {
        $session = $this->findUserSession($id);

        if ($session->status !== 'running') {
            return redirect()->route('driver.charging.receipt', $session->id_session);
        }

        $session->load(['charger.location', 'reservation.vehicle']);

        return view('driver.charging.show', [
            'session' => $session,
            'tariff' => $session->charger->location?->currentTariff(),
            'bill' => $session->computeBill(),
        ]);
    }

    public function getTelemetry($id)
    {
        $session = $this->findUserSession($id);
        $balance = (float) auth()->user()->balance;

        if ($session->status !== 'running') {
            return response()->json([
                'status' => $session->status,
                'finished' => true,
                'redirect' => route('driver.charging.receipt', $session->id_session),
            ]);
        }

        $bill = $session->computeBill();

        $session->update(['energi_kwh' => $bill['kwh'], 'estimasi_biaya' => $bill['total']]);

        $s = $bill['seconds'];

        return response()->json([
            'status' => 'running',
            'finished' => false,
            'energy_kwh' => $bill['kwh'],
            'total_cost' => $bill['total'],
            'power_kw' => $bill['power_kw'],
            'rate' => $bill['rate'],
            'overstay_minutes' => $bill['overstay_minutes'],
            'duration_seconds' => $s,
            'formatted_time' => sprintf('%02d:%02d:%02d', intdiv($s, 3600), intdiv($s % 3600, 60), $s % 60),
            'balance' => $balance,
            'low_balance' => $bill['total'] >= $balance * 0.85,
        ]);
    }

    public function stopSession($id)
    {
        $session = $this->findUserSession($id);

        $result = $session->settle('completed');

        if ($result === null) {
            return redirect()->route('driver.charging.receipt', $session->id_session)->with('info', 'Transaksi ini sudah selesai.');
        }

        $redirect = redirect()->route('driver.charging.receipt', $session->id_session)
            ->with('success', 'Pengisian selesai. Pembayaran dipotong otomatis dari saldo.');

        if ($result['debt'] > 0) {
            $redirect->with('error', 'Saldo kurang Rp ' . number_format($result['debt'], 0, ',', '.') . '. Tagihan tercatat dan akan dilunasi otomatis saat Anda top up.');
        }

        return $redirect;
    }

    public function showReceipt($id)
    {
        $session = ChargingSession::with(['charger.location', 'reservation.vehicle'])
            ->where('user_id', auth()->id())->where('id_session', $id)->firstOrFail();

        if ($session->status === 'running') {
            return redirect()->route('driver.charging.monitor', $session->id_session);
        }

        return view('driver.charging.receipt', [
            'session' => $session,
            'payments' => Payment::where('id_session', $session->id_session)->get(),
            // Satu ulasan per sesi (bukan per lokasi).
            'reviewExists' => Review::where('user_id', auth()->id())
                ->where('id_session', $session->id_session)
                ->exists(),
        ]);
    }

    public function history()
    {
        $base = ChargingSession::where('user_id', auth()->id())->whereIn('status', ['completed', 'interrupted']);

        return view('driver.charging.history', [
            'transactions' => (clone $base)->with(['charger.location', 'reservation.vehicle'])->orderByDesc('waktu_mulai')->paginate(10),
            'summary' => [
                'count' => (clone $base)->count(),
                'kwh' => (float) (clone $base)->sum('energi_kwh'),
                'spend' => (float) (clone $base)->sum('estimasi_biaya'),
            ],
        ]);
    }
}