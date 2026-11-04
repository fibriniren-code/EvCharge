<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Charger;
use App\Models\ChargingSession;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChargingSessionController extends Controller
{
    /**
     * Mencari sesi charging milik pengguna yang sedang login.
     * Pencarian mendukung ID sesi maupun ID reservasi.
     */
    private function findUserSession($id)
    {
        return ChargingSession::where('user_id', auth()->id())
            ->where(function ($query) use ($id) {
                $query->where('id_session', $id)
                    ->orWhere('id_reservation', $id);
            })
            ->firstOrFail();
    }

    /**
     * Memproses QR Code atau ID Booking.
     */
    public function processScan(Request $request)
    {
        $request->validate([
            'qr_code' => 'nullable|string',
            'booking_id' => 'nullable|string',
        ]);

        if (!$request->qr_code && !$request->booking_id) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan scan QR Code atau masukkan ID Booking / Reservasi Anda.'
            ], 422);
        }

        $userId = auth()->id();
        $reservation = null;
        $charger = null;

        if ($request->booking_id) {
            $reservation = Reservation::where('user_id', $userId)
                ->where('id_reservation', $request->booking_id)
                ->whereIn('status', ['confirmed', 'active', 'pending'])
                ->first();

            if (!$reservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID Booking tidak ditemukan atau statusnya sudah tidak aktif.'
                ], 404);
            }

            $charger = Charger::find($reservation->charger_id);
        } elseif ($request->qr_code) {
            $charger = Charger::where('qr_code', $request->qr_code)->first();

            if (!$charger) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mesin charger tidak ditemukan.'
                ], 404);
            }

            $reservation = Reservation::where('user_id', $userId)
                ->where('charger_id', $charger->id_charger)
                ->whereIn('status', ['confirmed', 'active', 'pending'])
                ->latest('id_reservation')
                ->first();

            if (!$reservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki reservasi aktif pada mesin charger ini.'
                ], 400);
            }
        }

        if ($charger && in_array(strtolower($charger->status), ['faulted', 'error'])) {
            return response()->json([
                'success' => false,
                'message' => 'Port charger sedang mengalami gangguan teknis.'
            ], 422);
        }

        if ($charger && in_array(strtolower($charger->status), ['unavailable', 'maintenance'])) {
            return response()->json([
                'success' => false,
                'message' => 'Port charger sedang dalam pemeliharaan.'
            ], 422);
        }

        if ($charger && strtolower($charger->status) === 'occupied') {
            return response()->json([
                'success' => false,
                'message' => 'Port charger sedang digunakan oleh pengemudi lain.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'charger_id' => $charger->id_charger,
            'reservation_id' => $reservation->id_reservation,
        ]);
    }

    /**
     * Memulai sesi charging.
     */
    public function startSession(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id_reservation',
            'charging_type' => 'required|in:full,kwh,nominal',
            'target_value' => 'nullable|numeric|min:1',
        ]);

        $reservation = Reservation::where('user_id', auth()->id())
            ->findOrFail($request->reservation_id);

        return DB::transaction(function () use ($reservation) {
            $charger = Charger::findOrFail($reservation->charger_id);

            $charger->update([
                'status' => 'occupied'
            ]);

            $reservation->update([
                'status' => 'active'
            ]);

            $session = ChargingSession::create([
                'user_id' => $reservation->user_id,
                'id_charger' => $reservation->charger_id,
                'id_reservation' => $reservation->id_reservation,
                'waktu_mulai' => now(),
                'energi_kwh' => 0,
                'estimasi_biaya' => 0,
                'status' => 'running',
            ]);

            return redirect()
                ->route('driver.charging.monitor', $session->id_session)
                ->with('success', 'Pengisian daya berhasil dimulai.');
        });
    }

    /**
     * Menampilkan halaman monitoring charging.
     */
    public function monitorSession($id)
    {
        $session = $this->findUserSession($id);

        $session->load([
            'charger.location',
            'reservation.vehicle',
        ]);

        return view('driver.charging.show', compact('session'));
    }

    /**
     * Telemetry pengisian daya.
     */
    public function getTelemetry($id)
    {
        $session = $this->findUserSession($id);

        if ($session->status !== 'running') {
            return response()->json([
                'status' => $session->status,
                'energy_kwh' => $session->energi_kwh,
                'total_cost' => $session->estimasi_biaya,
                'duration' => $session->waktu_mulai && $session->waktu_selesai
                    ? Carbon::parse($session->waktu_mulai)
                        ->diffInMinutes(Carbon::parse($session->waktu_selesai))
                    : 0,
            ]);
        }

        $startTime = Carbon::parse($session->waktu_mulai);
        $secondsElapsed = max(1, $startTime->diffInSeconds(now()));

        $session->loadMissing('charger');

        $powerKw = $session->charger->power_kw ?? 22;
        $kwhDelivered = round(($powerKw / 3600) * $secondsElapsed, 2);

        $totalCost = round($kwhDelivered * 2466);

        $session->update([
            'energi_kwh' => $kwhDelivered,
            'estimasi_biaya' => $totalCost,
        ]);

        return response()->json([
            'status' => $session->status,
            'energy_kwh' => $kwhDelivered,
            'total_cost' => $totalCost,
            'duration_seconds' => $secondsElapsed,
            'formatted_time' => sprintf(
                '%02d:%02d:%02d',
                floor($secondsElapsed / 3600),
                floor(($secondsElapsed % 3600) / 60),
                $secondsElapsed % 60
            ),
        ]);
    }

    /**
     * Menghentikan charging dan memotong saldo.
     */
    public function stopSession($id)
    {
        $session = $this->findUserSession($id);

        if ($session->status !== 'running') {
            return redirect()
                ->route('driver.charging.receipt', $session->id_session)
                ->with('info', 'Transaksi ini sudah selesai.');
        }

        $session->load([
            'charger',
            'reservation'
        ]);

        $endTime = now();
        $startTime = Carbon::parse($session->waktu_mulai);

        $secondsElapsed = max(5, $startTime->diffInSeconds($endTime));

        $powerKw = $session->charger->power_kw ?? 22;

        $kwhDelivered = round(($powerKw / 3600) * $secondsElapsed, 2);

        if ($kwhDelivered <= 0) {
            $kwhDelivered = 0.05;
        }

        $totalCost = round($kwhDelivered * 2466);

        DB::transaction(function () use (
            $session,
            $totalCost,
            $endTime,
            $kwhDelivered
        ) {
            $lockedUser = User::where('id', auth()->id())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedUser->balance < $totalCost) {
                throw new \RuntimeException('Saldo tidak mencukupi.');
            }

            $lockedUser->decrement('balance', $totalCost);

            $session->update([
                'waktu_selesai' => $endTime,
                'energi_kwh' => $kwhDelivered,
                'estimasi_biaya' => $totalCost,
                'status' => 'completed',
            ]);

            if ($session->charger) {
                $session->charger->update([
                    'status' => 'available'
                ]);
            }

            if ($session->reservation) {
                $session->reservation->update([
                    'status' => 'completed'
                ]);
            }
        });

        return redirect()
            ->route('driver.charging.receipt', $session->id_session)
            ->with('success', 'Pengisian daya selesai dan pembayaran berhasil dipotong dari saldo.');
    }

    /**
     * Menampilkan struk transaksi.
     */
    public function showReceipt($id)
    {
        $session = ChargingSession::with([
            'charger.location',
            'reservation.vehicle',
        ])
            ->where('user_id', auth()->id())
            ->where('id_session', $id)
            ->firstOrFail();

        return view('driver.charging.receipt', compact('session'));
    }

    /**
     * Menampilkan riwayat transaksi pengguna.
     */
    public function history()
    {
        $transactions = ChargingSession::with([
            'charger.location',
            'reservation.vehicle',
        ])
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->orderByDesc('waktu_mulai')
            ->paginate(10);

        return view('driver.charging.history', compact('transactions'));
    }
}
