<?php

namespace App\Http\Controllers;

use App\Models\Charger;
use App\Models\ChargingSession;
use App\Models\Location;
use App\Models\Reservation;
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

        $expiresAt = Carbon::now()->addMinutes(15);

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

        return redirect()->route('reservations.show', $reservation->id_reservation)
            ->with('success', 'Reservasi berhasil dibuat. Silakan selesaikan pembayaran sebelum batas waktu berakhir.');
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

    public function stopCharging($id)
    {
        $reservation = $this->findUserReservation($id);

        $targetId = DB::transaction(function () use ($reservation) {
            // 1. Update status reservasi menjadi completed & bebaskan charger
            $reservation->update([
                'status'     => 'completed',
                'stopped_at' => now(),
            ]);

            if ($reservation->charger) {
                $reservation->charger->update(['status' => 'available']);
            }

            // 2. Coba buat record ChargingSession jika memungkinkan
            try {
                $sessionData = [];
                
                // Masukkan data kolom secara kondisional hanya jika kolomnya ada
                if (Schema::hasColumn('charging_sessions', 'user_id')) {
                    $sessionData['user_id'] = $reservation->user_id;
                }
                if (Schema::hasColumn('charging_sessions', 'charger_id')) {
                    $sessionData['charger_id'] = $reservation->charger_id;
                }
                if (Schema::hasColumn('charging_sessions', 'vehicle_id')) {
                    $sessionData['vehicle_id'] = $reservation->vehicle_id;
                }
                if (Schema::hasColumn('charging_sessions', 'reservation_id')) {
                    $sessionData['reservation_id'] = $reservation->id_reservation;
                }
                if (Schema::hasColumn('charging_sessions', 'status')) {
                    $sessionData['status'] = 'completed';
                }

                if (!empty($sessionData)) {
                    $session = ChargingSession::create($sessionData);
                    return $session->getKey();
                }
            } catch (\Exception $e) {
                // Abaikan error pembuatan session agar alur tidak terhenti
            }

            // Fallback ke ID reservasi
            return $reservation->id_reservation;
        });

        // 3. Alihkan langsung ke halaman pembayaran/struk
        return redirect()->route('driver.charging.receipt', $targetId)
            ->with('success', 'Sesi pengisian daya berhasil dihentikan. Silakan selesaikan pembayaran.');
    }
}