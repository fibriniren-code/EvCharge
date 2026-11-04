<?php

namespace App\Http\Controllers;

use App\Models\Charger;
use App\Models\ChargingSession;
use App\Models\Location;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['charger.location', 'vehicle'])
            ->where('user_id', auth()->id())
            ->latest('id_reservation')
            ->get();

        return view('driver.reservations.index', compact('reservations'));
    }

    public function create($location, $charger)
    {
        $locationModel = Location::findOrFail($location);
        $chargerModel  = Charger::where('location_id', $locationModel->id_location)
                                 ->findOrFail($charger);

        $vehicles = Vehicle::where('user_id', auth()->id())->get();

        return view('driver.reservations.create', [
            'location' => $locationModel,
            'charger'  => $chargerModel,
            'vehicles' => $vehicles,
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->filled('vehicle_id')) {
            $firstVehicle = Vehicle::where('user_id', auth()->id())->first();

            if (!$firstVehicle) {
                return back()->withErrors([
                    'vehicle_id' => 'Anda belum memiliki kendaraan terdaftar. Silakan tambahkan kendaraan di menu Mobil Saya terlebih dahulu.'
                ])->withInput();
            }

            $request->merge(['vehicle_id' => $firstVehicle->id_vehicle]);
        }

        $validated = $request->validate([
            'charger_id'     => 'required|exists:chargers,id_charger',
            'vehicle_id'     => 'required|exists:vehicles,id_vehicle',
            'start_time'     => 'required|date|after_or_equal:now',
            'duration_hours' => 'required|integer|min:1|max:24',
        ]);

        $charger = Charger::findOrFail($validated['charger_id']);

        if ($charger->status !== 'available') {
            return back()->withErrors(['charger_id' => 'Charger sedang tidak tersedia untuk dipesan.'])->withInput();
        }

        $startTime = Carbon::parse($validated['start_time']);
        $endTime   = (clone $startTime)->addHours((int) $validated['duration_hours']);

        $hasOverlap = Reservation::where('charger_id', $charger->id_charger)
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('waktu_mulai', [$startTime, $endTime])
                      ->orWhereBetween('waktu_selesai', [$startTime, $endTime])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('waktu_mulai', '<=', $startTime)
                            ->where('waktu_selesai', '>=', $endTime);
                      });
            })
            ->exists();

        if ($hasOverlap) {
            return back()->withErrors(['start_time' => 'Slot waktu yang dipilih sudah dipesan pengguna lain.'])->withInput();
        }

        // Holding Slot diset MAX 10 MENIT
        $expiresAt = Carbon::now()->addMinutes(10);

        $reservation = DB::transaction(function () use ($validated, $charger, $startTime, $endTime, $expiresAt) {
            $res = Reservation::create([
                'user_id'       => auth()->id(),
                'charger_id'    => $charger->id_charger,
                'vehicle_id'    => $validated['vehicle_id'],
                'waktu_mulai'   => $startTime,
                'waktu_selesai' => $endTime,
                'expires_at'    => $expiresAt,
                'status'        => 'pending',
            ]);

            $charger->update(['status' => 'reserved']);

            return $res;
        });

        // Redirect ke detail reservasi agar driver dapat melihat ID Booking / Catatan terlebih dahulu
        return redirect()->route('reservations.show', $reservation->id_reservation)
            ->with('success', 'Reservasi berhasil dibuat. Slot Anda ditahan selama 10 menit.');
    }

    private function findUserReservation($id)
    {
        return Reservation::where('user_id', auth()->id())
            ->where('id_reservation', $id)
            ->firstOrFail();
    }

    public function show($id)
    {
        $reservation = $this->findUserReservation($id);
        $reservation->load(['charger.location', 'vehicle']);

        return view('driver.reservations.show', compact('reservation'));
    }

    public function getTimer($id)
    {
        $reservation = $this->findUserReservation($id);

        $remainingSeconds = Carbon::now()->diffInSeconds($reservation->expires_at, false);

        return response()->json([
            'status'     => $reservation->status,
            'expires_at' => $reservation->expires_at,
            'remaining'  => max(0, $remainingSeconds),
            'is_expired' => $remainingSeconds <= 0 && $reservation->status === 'pending',
        ]);
    }

    public function confirm($id)
    {
        $reservation = $this->findUserReservation($id);

        if ($reservation->status === 'pending') {
            if (Carbon::now()->greaterThan($reservation->expires_at)) {
                $reservation->update(['status' => 'expired']);
                if ($reservation->charger) {
                    $reservation->charger->update(['status' => 'available']);
                }
                return back()->withErrors(['error' => 'Waktu konfirmasi pembayaran telah kadaluarsa.']);
            }

            $reservation->update(['status' => 'confirmed']);
            return redirect()->route('reservations.show', $reservation->id_reservation)
                ->with('success', 'Reservasi berhasil dikonfirmasi.');
        }

        return back()->withErrors(['error' => 'Reservasi tidak dapat dikonfirmasi.']);
    }

    public function cancel($id)
    {
        $reservation = $this->findUserReservation($id);

        if (in_array($reservation->status, ['pending', 'confirmed'])) {
            DB::transaction(function () use ($reservation) {
                $reservation->update(['status' => 'cancelled']);

                if ($reservation->charger) {
                    $reservation->charger->update(['status' => 'available']);
                }
            });

            return redirect()->route('reservations.index')
                ->with('success', 'Reservasi berhasil dibatalkan.');
        }

        return back()->withErrors(['error' => 'Reservasi ini tidak dapat dibatalkan.']);
    }

    public function getSlotStatuses($location_id)
    {
        Reservation::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get()
            ->each(function ($res) {
                $res->update(['status' => 'expired']);
                if ($res->charger) {
                    $res->charger->update(['status' => 'available']);
                }
            });

        $chargers = Charger::where('location_id', $location_id)
            ->withCount(['reservations as active_reservations_count' => function ($query) {
                $query->whereIn('status', ['pending', 'confirmed', 'active'])
                      ->where('expires_at', '>', now());
            }])
            ->get();

        return response()->json($chargers);
    }

    /**
     * Stop Charging: Hitung Tagihan, Cek Saldo & Auto-Debit Pemotongan Saldo
     */
    public function stopCharging($id)
    {
        $reservation = $this->findUserReservation($id);
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // 1. Kalkulasi Durasi & Tagihan Biaya Berbasis Detik/Menit Minimal
        $startTime = Carbon::parse($reservation->waktu_mulai);
        $endTime   = now();
        $secondsElapsed = max(5, $startTime->diffInSeconds($endTime));
        $durationMinutes = max(1, ceil($secondsElapsed / 60));

        $powerKw = $reservation->charger->power_kw ?? 22;
        $kwhDelivered = round(($powerKw / 3600) * $secondsElapsed, 2);
        if ($kwhDelivered <= 0) {
            $kwhDelivered = 0.05;
        }

        $totalCost = round($kwhDelivered * 2466);

        // 2. PENGECEKAN KECUKUPAN SALDO
        if ($user->balance < $totalCost) {
            $kekurangan = $totalCost - $user->balance;
            return redirect()->route('topup.index')
                ->with('error', 'Saldo Anda kurang (Rp ' . number_format($kekurangan, 0, ',', '.') . ') untuk membayar pengisian ini. Silakan Top Up terlebih dahulu.');
        }

        // 3. JIKA SALDO CUKUP: Eksekusi Auto-Debit dan Update Status
        $targetId = DB::transaction(function () use ($reservation, $user, $totalCost, $endTime, $durationMinutes, $kwhDelivered) {
            // Potong Saldo Akun User
            DB::table('users')->where('id', $user->id)->decrement('balance', $totalCost);

            // Update status reservasi & charger
            $reservation->update([
                'status'     => 'completed',
                'stopped_at' => $endTime,
            ]);

            if ($reservation->charger) {
                $reservation->charger->update(['status' => 'available']);
            }

            // Buat Record Sesi Pengisian (Dukungan penuh nama kolom alternatif)
            try {
                $columns = Schema::getColumnListing('charging_sessions');
                $sessionData = [];

                if (in_array('user_id', $columns)) {
                    $sessionData['user_id'] = $reservation->user_id;
                }
                
                if (in_array('charger_id', $columns)) {
                    $sessionData['charger_id'] = $reservation->charger_id;
                } elseif (in_array('id_charger', $columns)) {
                    $sessionData['id_charger'] = $reservation->charger_id;
                }

                if (in_array('vehicle_id', $columns)) {
                    $sessionData['vehicle_id'] = $reservation->vehicle_id;
                } elseif (in_array('id_vehicle', $columns)) {
                    $sessionData['id_vehicle'] = $reservation->vehicle_id;
                }

                if (in_array('reservation_id', $columns)) {
                    $sessionData['reservation_id'] = $reservation->id_reservation;
                } elseif (in_array('id_reservation', $columns)) {
                    $sessionData['id_reservation'] = $reservation->id_reservation;
                }

                if (in_array('start_time', $columns)) {
                    $sessionData['start_time'] = $reservation->waktu_mulai;
                } elseif (in_array('waktu_mulai', $columns)) {
                    $sessionData['waktu_mulai'] = $reservation->waktu_mulai;
                }

                if (in_array('end_time', $columns)) {
                    $sessionData['end_time'] = $endTime;
                } elseif (in_array('waktu_selesai', $columns)) {
                    $sessionData['waktu_selesai'] = $endTime;
                }

                if (in_array('energy_kwh', $columns)) {
                    $sessionData['energy_kwh'] = $kwhDelivered;
                } elseif (in_array('energy_consumed', $columns)) {
                    $sessionData['energy_consumed'] = $kwhDelivered;
                }

                if (in_array('duration_minutes', $columns)) {
                    $sessionData['duration_minutes'] = $durationMinutes;
                }

                if (in_array('total_cost', $columns)) {
                    $sessionData['total_cost'] = $totalCost;
                }

                // Cek Enum Status Aset
                if (in_array('status', $columns)) {
                    $sessionData['status'] = 'completed';
                }

                if (!empty($sessionData)) {
                    $session = ChargingSession::create($sessionData);
                    return $session->getKey();
                }
            } catch (\Exception $e) {
                // Abaikan jika penulisan log charging_sessions gagal
            }

            return $reservation->id_reservation;
        });

        // 4. Sinkronkan Session Auth User
        auth()->setUser(User::find($user->id));

        // 5. Direct Langsung ke Struk Digital Resmi
        return redirect()->route('driver.charging.receipt', $targetId)
            ->with('success', 'Sesi pengisian daya selesai dan pembayaran berhasil dipotong dari saldo.');
    }
}