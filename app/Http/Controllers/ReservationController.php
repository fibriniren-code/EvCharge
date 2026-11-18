<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Driver\ChargingSessionController;
use App\Models\Charger;
use App\Models\Location;
use App\Models\Reservation;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public const HOLD_HOURS = 3;

    public function index()
    {
        Reservation::expireStale();

        $user = auth()->user();

        $reservations = Reservation::with([
            'charger.location',
            'vehicle',
            'chargingSession',
        ])
            ->where('user_id', $user->id)
            ->latest('id_reservation')
            ->get();

        $user = $user->fresh();

        $blockedUntil = $user->reservation_blocked_until;
        $noShowCount = $user->no_show_count;

        return view('driver.reservations.index', compact(
            'reservations',
            'blockedUntil',
            'noShowCount'
        ));
    }

    public function create($location, $charger)
    {
        Reservation::expireStale();

        $user = auth()->user()->fresh();

        if ($user->isReservationBlocked()) {
            return redirect()->route('reservations.index')
                ->withErrors([
                    'error' => 'Anda sementara tidak dapat membuat reservasi karena sudah mencapai 3 kali no-show. Pembatasan berakhir pada ' .
                        $user->reservation_blocked_until->format('d-m-Y H:i') . '.',
                ]);
        }

        $locationModel = Location::findOrFail($location);

        $chargerModel = Charger::where(
            'location_id',
            $locationModel->id_location
        )->findOrFail($charger);

        if (! $locationModel->isOpen()) {
            return redirect()->route('spklu.show', $locationModel->id_location)
                ->withErrors([
                    'error' => 'Stasiun sedang ditutup sementara.',
                ]);
        }

        if ($chargerModel->status !== 'available') {
            return redirect()->route('spklu.show', $locationModel->id_location)
                ->withErrors([
                    'error' => 'Charger ini sedang tidak tersedia untuk dipesan.',
                ]);
        }

        // Ambil semua kendaraan milik user tanpa memeriksa jenis konektor.
        $vehicles = Vehicle::where('user_id', $user->id)->get();

        if ($vehicles->isEmpty()) {
            return redirect()->route('cars.create')
                ->with('info', 'Tambahkan kendaraan terlebih dahulu sebelum memesan slot.');
        }

        return view('driver.reservations.create', [
            'location' => $locationModel,
            'charger' => $chargerModel,
            'vehicles' => $vehicles,
            'tariff' => $locationModel->currentTariff(),
            'holdHours' => self::HOLD_HOURS,
        ]);
    }

    public function store(Request $request)
    {
        Reservation::expireStale();

        $user = $request->user()->fresh();

        if ($user->isReservationBlocked()) {
            return redirect()->route('reservations.index')
                ->withErrors([
                    'error' => 'Anda belum dapat membuat reservasi. Akun dibatasi sampai ' .
                        $user->reservation_blocked_until->format('d-m-Y H:i') . '.',
                ]);
        }

        $validated = $request->validate([
            'charger_id' => [
                'required',
                'exists:chargers,id_charger',
            ],
            'vehicle_id' => [
                'required',
                Rule::exists('vehicles', 'id_vehicle')
                    ->where('user_id', $user->id),
            ],
            'start_time' => [
                'required',
                'date',
                'after_or_equal:now',
                'before_or_equal:+' . self::HOLD_HOURS . ' hours',
            ],
            'duration_hours' => [
                'required',
                'integer',
                'min:1',
                'max:4',
            ],
        ], [
            'vehicle_id.required' => 'Pilih kendaraan yang akan diisi dayanya.',
            'start_time.after_or_equal' => 'Waktu mulai tidak boleh di masa lalu.',
            'start_time.before_or_equal' => 'Waktu mulai maksimal ' .
                self::HOLD_HOURS . ' jam dari sekarang.',
        ]);

        $charger = Charger::with('location')
            ->findOrFail($validated['charger_id']);

        // Pastikan kendaraan memang milik user.
        // Tidak ada pemeriksaan kecocokan konektor.
        $vehicle = Vehicle::where('user_id', $user->id)
            ->findOrFail($validated['vehicle_id']);

        if (! $charger->location->isOpen()) {
            return back()
                ->withErrors([
                    'error' => 'Stasiun sedang ditutup sementara.',
                ])
                ->withInput();
        }

        if ($user->outstandingAmount() > 0) {
            return redirect()->route('topup.index')
                ->withErrors([
                    'error' => 'Selesaikan tagihan tertunggak terlebih dahulu.',
                ]);
        }

        if (Reservation::where('user_id', $user->id)->holding()->exists()) {
            return back()
                ->withErrors([
                    'error' => 'Anda masih memiliki reservasi aktif. Selesaikan atau batalkan dahulu.',
                ])
                ->withInput();
        }

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = (clone $startTime)
            ->addHours((int) $validated['duration_hours']);

        $reservation = DB::transaction(function () use (
            $charger,
            $vehicle,
            $user,
            $startTime,
            $endTime
        ) {
            $locked = Charger::whereKey($charger->id_charger)
                ->lockForUpdate()
                ->first();

            if (! $locked || $locked->status !== 'available') {
                return null;
            }

            $created = Reservation::create([
                'user_id' => $user->id,
                'charger_id' => $locked->id_charger,
                'vehicle_id' => $vehicle->id_vehicle,
                'waktu_mulai' => $startTime,
                'waktu_selesai' => $endTime,
                'expires_at' => now()->addHours(self::HOLD_HOURS),
                'status' => 'pending',
            ]);

            $locked->update([
                'status' => 'reserved',
            ]);

            return $created;
        });

        if (! $reservation) {
            return back()
                ->withErrors([
                    'error' => 'Maaf, charger baru saja dipesan pengguna lain.',
                ])
                ->withInput();
        }

        return redirect()->route(
            'reservations.show',
            $reservation->id_reservation
        )->with(
            'success',
            'Reservasi dibuat. Konfirmasi dalam ' .
                self::HOLD_HOURS . ' jam agar slot tetap milik Anda.'
        );
    }

    private function findUserReservation($id): Reservation
    {
        return Reservation::where('user_id', auth()->id())
            ->where('id_reservation', $id)
            ->firstOrFail();
    }

    public function show($id)
    {
        Reservation::expireStale();

        $reservation = $this->findUserReservation($id);

        $reservation->load([
            'charger.location',
            'vehicle',
            'chargingSession',
        ]);

        return view('driver.reservations.show', [
            'reservation' => $reservation,
            'tariff' => $reservation->charger?->location?->currentTariff(),
            'minBalance' => ChargingSessionController::MIN_START_BALANCE,
        ]);
    }

    public function getTimer($id)
    {
        Reservation::expireStale();

        $reservation = $this->findUserReservation($id);

        $remaining = $reservation->expires_at
            ? max(0, (int) now()->diffInSeconds(
                $reservation->expires_at,
                false
            ))
            : 0;

        return response()->json([
            'status' => $reservation->status,
            'expires_at' => $reservation->expires_at?->toIso8601String(),
            'remaining_seconds' => $remaining,
            'is_expired' => in_array(
                $reservation->status,
                ['expired', 'cancelled'],
                true
            ),
        ]);
    }

    public function confirm($id)
    {
        Reservation::expireStale();

        $user = auth()->user()->fresh();

        if ($user->isReservationBlocked()) {
            return redirect()->route('reservations.index')
                ->withErrors([
                    'error' => 'Akun Anda sedang dibatasi untuk membuat reservasi sampai ' .
                        $user->reservation_blocked_until->format('d-m-Y H:i') . '.',
                ]);
        }

        $reservation = $this->findUserReservation($id);

        if ($reservation->status !== 'pending') {
            return back()
                ->withErrors([
                    'error' => 'Reservasi ini tidak dapat dikonfirmasi.',
                ]);
        }

        if (now()->greaterThanOrEqualTo($reservation->waktu_selesai)) {
            $reservation->update([
                'status' => 'expired',
            ]);

            $reservation->charger?->syncReservedState();

            return redirect()->route(
                'reservations.show',
                $reservation->id_reservation
            )->withErrors([
                'error' => 'Jadwal reservasi sudah terlewat.',
            ]);
        }

        if ((float) $user->balance < ChargingSessionController::MIN_START_BALANCE) {
            return redirect()->route('topup.index')
                ->withErrors([
                    'error' => 'Saldo minimal Rp ' .
                        number_format(
                            ChargingSessionController::MIN_START_BALANCE,
                            0,
                            ',',
                            '.'
                        ) .
                        ' dibutuhkan untuk mengonfirmasi reservasi. Silakan top up.',
                ]);
        }

        $reservation->update([
            'status' => 'confirmed',
            'expires_at' => $reservation->waktu_selesai,
        ]);

        return redirect()->route(
            'reservations.show',
            $reservation->id_reservation
        )->with([
            'success' => 'Reservasi terkonfirmasi. Scan QR di charger untuk memulai pengisian.',
            'show_no_show_rules' => true,
        ]);
    }

    public function cancel($id)
    {
        $reservation = $this->findUserReservation($id);

        if (! in_array($reservation->status, ['pending', 'confirmed'], true)) {
            return back()->withErrors([
                'error' => 'Reservasi ini tidak dapat dibatalkan.',
            ]);
        }

        DB::transaction(function () use ($reservation) {
            $reservation->update([
                'status' => 'cancelled',
            ]);

            $reservation->charger?->syncReservedState();
        });

        return redirect()->route('reservations.index')
            ->with('success', 'Reservasi berhasil dibatalkan.');
    }

    public function getSlotStatuses($location_id)
    {
        Reservation::expireStale();

        return response()->json(
            Charger::where('location_id', $location_id)
                ->get([
                    'id_charger',
                    'charger_type',
                    'power_kw',
                    'status',
                ])
        );
    }
}
