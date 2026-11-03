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
use Illuminate\Support\Facades\Schema;

class ChargingSessionController extends Controller
{
    /**
     * Helper privat untuk mencari sesi berdasarkan ID secara aman
     */
    private function findUserSession($id)
    {
        $columns = Schema::getColumnListing('charging_sessions');

        return ChargingSession::where('user_id', auth()->id())
            ->where(function ($q) use ($id, $columns) {
                if (in_array('id_session', $columns)) {
                    $q->where('id_session', $id);
                }
                if (in_array('id', $columns)) {
                    $q->orWhere('id', $id);
                }
            })
            ->firstOrFail();
    }

    /**
     * Langkah 1: Memproses Scan QR Code ATAU Input ID Booking Manual (FR-04, FR-08)
     */
    public function processScan(Request $request)
    {
        $request->validate([
            'qr_code'    => 'nullable|string',
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

        // OPSI A: Input ID Booking / Reservasi Manual
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
        } 
        // OPSI B: Scan QR Code Mesin SPKLU
        else if ($request->qr_code) {
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

        // Pengecekan Status Operasional Charger (FR-08)
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

            // Ubah Status Charger & Reservasi
            $charger->update(['status' => 'occupied']);
            
            try {
                $reservation->update(['status' => 'active']);
            } catch (\Exception $e) {
                try {
                    $reservation->update(['status' => 'confirmed']);
                } catch (\Exception $ex) {
                    // Fallback jika enum berbeda
                }
            }

            // Dapatkan daftar seluruh nama kolom di tabel charging_sessions secara akurat
            $columns = Schema::getColumnListing('charging_sessions');
            $now = now();
            $sessionData = [];

            // User ID
            if (in_array('user_id', $columns)) {
                $sessionData['user_id'] = $reservation->user_id;
            }

            // Vehicle ID
            if (in_array('vehicle_id', $columns)) {
                $sessionData['vehicle_id'] = $reservation->vehicle_id;
            } elseif (in_array('id_vehicle', $columns)) {
                $sessionData['id_vehicle'] = $reservation->vehicle_id;
            }

            // Charger ID
            if (in_array('charger_id', $columns)) {
                $sessionData['charger_id'] = $reservation->charger_id;
            } elseif (in_array('id_charger', $columns)) {
                $sessionData['id_charger'] = $reservation->charger_id;
            }

            // Reservation ID
            if (in_array('reservation_id', $columns)) {
                $sessionData['reservation_id'] = $reservation->id_reservation;
            } elseif (in_array('id_reservation', $columns)) {
                $sessionData['id_reservation'] = $reservation->id_reservation;
            }

            // Waktu Mulai
            if (in_array('start_time', $columns)) {
                $sessionData['start_time'] = $now;
            }
            if (in_array('waktu_mulai', $columns)) {
                $sessionData['waktu_mulai'] = $now;
            }

            // Waktu Selesai (Default 1 jam dari sekarang)
            if (in_array('waktu_selesai', $columns)) {
                $sessionData['waktu_selesai'] = (clone $now)->addHour();
            }
            if (in_array('end_time', $columns)) {
                $sessionData['end_time'] = (clone $now)->addHour();
            }

            // Metrics dasar
            if (in_array('charging_type', $columns)) {
                $sessionData['charging_type'] = $request->charging_type;
            }
            if (in_array('target_value', $columns)) {
                $sessionData['target_value'] = $request->target_value ?? 0;
            }
            if (in_array('energy_kwh', $columns)) {
                $sessionData['energy_kwh'] = 0;
            }
            if (in_array('energy_consumed', $columns)) {
                $sessionData['energy_consumed'] = 0;
            }
            if (in_array('total_cost', $columns)) {
                $sessionData['total_cost'] = 0;
            }

            // Dapatkan record session dan hindari error data truncated enum
            $session = null;
            $possibleStatuses = ['charging', 'active', 'in_progress', 'process', 'ongoing', '1'];

            if (in_array('status', $columns)) {
                foreach ($possibleStatuses as $statusValue) {
                    try {
                        $testData = $sessionData;
                        $testData['status'] = $statusValue;
                        $session = ChargingSession::create($testData);
                        break;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            if (!$session) {
                $session = ChargingSession::create($sessionData);
            }

            return redirect()->route('driver.charging.monitor', $session->getKey())
                ->with('success', 'Pengisian daya berhasil dimulai.');
        });
    }

    /**
     * Monitoring Pengisian Daya Real-time
     */
    public function monitorSession($id)
    {
        $session = $this->findUserSession($id);
        $session->load(['charger.location', 'vehicle']);

        return view('driver.charging.show', compact('session'));
    }

    /**
     * Telemetry Simulasi kWh Mengalir Real-time (FR-05)
     */
    public function getTelemetry($id)
    {
        $session = $this->findUserSession($id);

        if (in_array($session->status, ['completed', 'stopped', 'done'])) {
            return response()->json([
                'status'     => 'completed',
                'energy_kwh' => $session->energy_kwh ?? $session->energy_consumed ?? 0,
                'total_cost' => $session->total_cost,
                'duration'   => $session->duration_minutes ?? 0,
            ]);
        }

        // Perhitungan durasi pengisian
        $startTime = Carbon::parse($session->start_time ?? $session->waktu_mulai ?? now());

        $secondsElapsed = max(1, $startTime->diffInSeconds(now()));
        
        $powerKw = $session->charger->power_kw ?? 22;
        $kwhDelivered = round(($powerKw / 3600) * $secondsElapsed, 2);
        
        $tarifPerKwh = 2466;
        $totalCost = round($kwhDelivered * $tarifPerKwh);

        // Perbarui data akumulasi secara aman
        $columns = Schema::getColumnListing('charging_sessions');
        $updateData = [];

        if (in_array('energy_kwh', $columns)) {
            $updateData['energy_kwh'] = $kwhDelivered;
        }
        if (in_array('energy_consumed', $columns)) {
            $updateData['energy_consumed'] = $kwhDelivered;
        }
        if (in_array('total_cost', $columns)) {
            $updateData['total_cost'] = $totalCost;
        }

        if (!empty($updateData)) {
            $session->update($updateData);
        }

        return response()->json([
            'status'           => $session->status ?? 'charging',
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
        $session = $this->findUserSession($id);
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $endTime = now();
        $startTime = Carbon::parse($session->start_time ?? $session->waktu_mulai ?? $endTime);
        
        // 1. Dapatkan durasi dalam DETIK agar pengisian singkat tetap terhitung
        $secondsElapsed = max(5, $startTime->diffInSeconds($endTime)); // Minimal dianggap 5 detik
        $durationMinutes = max(1, ceil($secondsElapsed / 60)); // Minimal 1 menit

        $powerKw = $session->charger->power_kw ?? 22;
        
        // 2. Hitung total kWh berbasis detik (dengan batas minimal kWh)
        $kwhDelivered = round(($powerKw / 3600) * $secondsElapsed, 2);
        if ($kwhDelivered <= 0) {
            $kwhDelivered = 0.05; // Nilai minimal kWh jika tes instan
        }

        // 3. Total Biaya
        $totalCost = round($kwhDelivered * 2466);

        // Pengecekan Ketersediaan Saldo
        if ($user->balance < $totalCost) {
            $kekurangan = $totalCost - $user->balance;
            return redirect()->route('topup.index')
                ->with('error', 'Saldo Anda tidak mencukupi (Kurang Rp ' . number_format($kekurangan, 0, ',', '.') . '). Silakan Top Up terlebih dahulu.');
        }

        // Eksekusi Pemotongan Saldo & Simpan Data secara Atomic
        DB::transaction(function () use ($session, $user, $totalCost, $endTime, $durationMinutes, $kwhDelivered) {
            // Potong Saldo Akun Driver
            DB::table('users')->where('id', $user->id)->decrement('balance', $totalCost);

            $columns = Schema::getColumnListing('charging_sessions');
            $updateData = [];

            if (in_array('status', $columns)) {
                $updateData['status'] = 'completed';
            }
            if (in_array('end_time', $columns)) {
                $updateData['end_time'] = $endTime;
            }
            if (in_array('waktu_selesai', $columns)) {
                $updateData['waktu_selesai'] = $endTime;
            }
            if (in_array('duration_minutes', $columns)) {
                $updateData['duration_minutes'] = $durationMinutes;
            }
            if (in_array('energy_kwh', $columns)) {
                $updateData['energy_kwh'] = $kwhDelivered;
            }
            if (in_array('energy_consumed', $columns)) {
                $updateData['energy_consumed'] = $kwhDelivered;
            }
            if (in_array('total_cost', $columns)) {
                $updateData['total_cost'] = $totalCost;
            }

            if (!empty($updateData)) {
                try {
                    $session->update($updateData);
                } catch (\Exception $e) {
                    // Fallback
                }
            }

            if ($session->charger) {
                $session->charger->update(['status' => 'available']);
            }

            if ($session->reservation) {
                try {
                    $session->reservation->update(['status' => 'completed']);
                } catch (\Exception $e) {
                    // Fallback
                }
            }
        });

        return redirect()->route('driver.charging.receipt', $session->getKey())
            ->with('success', 'Pengisian daya telah selesai dan pembayaran berhasil dipotong dari saldo.');
    }

    /**
     * Tampilan Struk / Resi Digital Bukti Transaksi Resmi (FR-06)
     */
    public function showReceipt($id)
    {
        $userId = auth()->id();
        $session = null;

        try {
            $session = $this->findUserSession($id);
            $session->load(['charger.location', 'vehicle']);
        } catch (\Exception $e) {
            $reservation = Reservation::with(['charger.location', 'vehicle'])
                ->where('user_id', $userId)
                ->where('id_reservation', $id)
                ->firstOrFail();

            $startTime = Carbon::parse($reservation->waktu_mulai);
            $endTime = $reservation->stopped_at ? Carbon::parse($reservation->stopped_at) : now();
            $durationMinutes = max(1, $startTime->diffInMinutes($endTime));
            $kwhDelivered = round(($durationMinutes / 60) * 22, 2);
            $totalCost = round($kwhDelivered * 2466);

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
        }

        // Refresh instance User agar saldo terbaru langsung muncul di navbar atas
        auth()->setUser(User::find($userId));

        return view('driver.charging.receipt', compact('session'));
    }
}