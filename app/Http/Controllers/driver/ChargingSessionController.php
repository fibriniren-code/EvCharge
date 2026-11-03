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
     * Langkah 1: Memproses Scan QR Code & Validasi Status Charger (FR-04, FR-08)
     */
    public function processScan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $charger = Charger::where('qr_code', $request->qr_code)->first();

        if (!$charger) {
            return response()->json([
                'success' => false,
                'message' => 'Mesin charger tidak ditemukan.'
            ], 404);
        }

        if (in_array(strtolower($charger->status), ['faulted', 'error'])) {
            return response()->json([
                'success' => false,
                'message' => 'Port charger sedang mengalami gangguan teknis.'
            ], 422);
        }

        if (in_array(strtolower($charger->status), ['unavailable', 'maintenance'])) {
            return response()->json([
                'success' => false,
                'message' => 'Port charger sedang tidak beroperasi / dalam pemeliharaan.'
            ], 422);
        }

        if (strtolower($charger->status) === 'occupied') {
            return response()->json([
                'success' => false,
                'message' => 'Port charger sedang digunakan oleh pengemudi lain.'
            ], 422);
        }

        $userId = auth()->id();
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

        return response()->json([
            'success'        => true,
            'charger_id'     => $charger->id_charger,
            'reservation_id' => $reservation->id_reservation,
        ]);
    }

    /**
     * Langkah 2 & 3: Inisiasi & Mulai Pengisian Daya (NFR-05 & FR-04)
     */
    public function startSession(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id_reservation',
            'charging_type'  => 'required|in:full,kwh,nominal',
            'target_value'   => 'nullable|numeric|min:1',
        ]);

        $reservation = Reservation::where('user_id', auth()->id())
            ->findOrFail($request->reservation_id);

        return DB::transaction(function () use ($reservation, $request) {
            $charger = Charger::findOrFail($reservation->charger_id);

            $charger->update(['status' => 'occupied']);
            $reservation->update(['status' => 'active']);

            $session = ChargingSession::create([
                'reservation_id' => $reservation->id_reservation,
                'user_id'        => $reservation->user_id,
                'charger_id'     => $reservation->charger_id,
                'vehicle_id'     => $reservation->vehicle_id,
                'start_time'     => now(),
                'status'         => 'charging',
                'charging_type'  => $request->charging_type,
                'target_value'   => $request->target_value ?? 0,
                'energy_kwh'     => 0,
                'total_cost'     => 0,
            ]);

            return redirect()->route('driver.charging.monitor', $session->id_session)
                ->with('success', 'Pengisian daya berhasil dimulai.');
        });
    }

    /**
     * Monitoring Pengisian Daya Real-time
     */
    public function monitorSession($id)
    {
        $session = ChargingSession::with(['charger.location', 'vehicle'])
            ->where('user_id', auth()->id())
            ->where('id_session', $id)
            ->firstOrFail();

        return view('driver.charging.show', compact('session'));
    }

    /**
     * Telemetry Simulasi kWh Mengalir Real-time (FR-05)
     */
    public function getTelemetry($id)
    {
        $session = ChargingSession::where('user_id', auth()->id())
            ->where('id_session', $id)
            ->firstOrFail();

        if ($session->status === 'completed') {
            return response()->json([
                'status'     => 'completed',
                'energy_kwh' => $session->energy_kwh,
                'total_cost' => $session->total_cost,
                'duration'   => $session->duration_minutes ?? 0,
            ]);
        }

        $startTime = Carbon::parse($session->start_time);
        $secondsElapsed = max(1, $startTime->diffInSeconds(now()));
        
        $powerKw = $session->charger->power_kw ?? 22;
        $kwhDelivered = round(($powerKw / 3600) * $secondsElapsed, 2);
        
        $tarifPerKwh = 2466;
        $totalCost = round($kwhDelivered * $tarifPerKwh);

        $session->update([
            'energy_kwh' => $kwhDelivered,
            'total_cost' => $totalCost,
        ]);

        return response()->json([
            'status'           => 'charging',
            'energy_kwh'       => $kwhDelivered,
            'total_cost'       => $totalCost,
            'duration_seconds' => $secondsElapsed,
            'formatted_time'   => sprintf('%02d:%02d:%02d', ($secondsElapsed/3600), ($secondsElapsed/60)%60, $secondsElapsed%60),
        ]);
    }

    /**
     * Menghentikan Pengisian Daya & Auto-Debit Pemotongan Saldo (FR-04 & FR-06)
     */
    public function stopSession($id)
    {
        $session = ChargingSession::where('user_id', auth()->id())
            ->where('id_session', $id)
            ->firstOrFail();

        $userId = auth()->id();

        DB::transaction(function () use ($session, $userId) {
            $endTime = now();
            $durationMinutes = Carbon::parse($session->start_time)->diffInMinutes($endTime);

            if ($session->total_cost <= 0) {
                $powerKw = $session->charger->power_kw ?? 22;
                $kwhDelivered = round(($powerKw / 60) * max(1, $durationMinutes), 2);
                $session->total_cost = round($kwhDelivered * 2466);
                $session->energy_kwh = $kwhDelivered;
            }

            // Potong Saldo Langsung di Database
            DB::table('users')->where('id', $userId)->decrement('balance', $session->total_cost);

            $session->update([
                'end_time'         => $endTime,
                'duration_minutes' => max(1, $durationMinutes),
                'status'           => 'completed',
            ]);

            if ($session->charger) {
                $session->charger->update(['status' => 'available']);
            }

            if ($session->reservation) {
                $session->reservation->update(['status' => 'completed']);
            }
        });

        return redirect()->route('driver.charging.receipt', $session->id_session)
            ->with('success', 'Pengisian daya telah selesai dan saldo Anda berhasil dipotong.');
    }

    /**
     * Tampilan Struk / Resi Digital Bukti Transaksi (FR-06)
     */
    public function showReceipt($id)
    {
        $userId = auth()->id();

        // 1. Cari Sesi Charging
        $session = ChargingSession::with(['charger.location', 'vehicle'])
            ->where('user_id', $userId)
            ->where('id_session', $id)
            ->first();

        if (!$session) {
            $reservation = Reservation::with(['charger.location', 'vehicle'])
                ->where('user_id', $userId)
                ->where('id_reservation', $id)
                ->firstOrFail();

            $startTime = Carbon::parse($reservation->waktu_mulai);
            $endTime = $reservation->stopped_at ? Carbon::parse($reservation->stopped_at) : now();
            $durationMinutes = max(1, $startTime->diffInMinutes($endTime));
            $kwhDelivered = round(($durationMinutes / 60) * 22, 2);
            $totalCost = round($kwhDelivered * 2466);

            // PEMOTONGAN SALDO LANGSUNG DI DATABASE TANPA PENGECUKAN STATUS IF
            DB::transaction(function () use ($userId, $totalCost, $reservation) {
                DB::table('users')->where('id', $userId)->decrement('balance', $totalCost);
                $reservation->update(['status' => 'completed']);
            });

            $session = (object) [
                'id_session'       => $reservation->id_reservation,
                'charger'          => $reservation->charger,
                'vehicle'          => $reservation->vehicle,
                'start_time'       => $startTime,
                'end_time'         => $endTime,
                'duration_minutes' => $durationMinutes,
                'energy_kwh'       => $kwhDelivered,
                'total_cost'       => $totalCost,
            ];
        } else {
            DB::transaction(function () use ($userId, $session) {
                DB::table('users')->where('id', $userId)->decrement('balance', $session->total_cost);
                $session->update(['status' => 'completed']);
            });
        }

        // 2. SINKRONISASI AKUN LOGIN UNTUK NAVBAR
        $freshUser = User::find($userId);
        auth()->setUser($freshUser);

        return view('driver.charging.receipt', compact('session'));
    }
}