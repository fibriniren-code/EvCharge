<?php

namespace App\Http\Controllers;

use App\Models\ChargingSession;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Admin dan operator diarahkan ke dashboard masing-masing; driver melihat beranda driver.
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'operator' => redirect()->route('operator.dashboard'),
            default => $this->driver($user),
        };
    }

    private function driver(User $user)
    {
        Reservation::expireStale();

        $activeSession = ChargingSession::with('charger.location')
            ->where('user_id', $user->id)
            ->where('status', 'running')
            ->latest('id_session')
            ->first();

        $activeReservation = Reservation::with(['charger.location', 'vehicle'])
            ->where('user_id', $user->id)
            ->holding()
            ->latest('id_reservation')
            ->first();

        $monthly = ChargingSession::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'interrupted'])
            ->where('waktu_mulai', '>=', now()->startOfMonth());

        $stats = [
            'sessions' => (clone $monthly)->count(),
            'kwh' => (float) (clone $monthly)->sum('energi_kwh'),
            'spend' => (float) (clone $monthly)->sum('estimasi_biaya'),
        ];

        $recent = ChargingSession::with('charger.location')
            ->where('user_id', $user->id)
            ->whereIn('status', ['completed', 'interrupted'])
            ->latest('id_session')
            ->take(5)
            ->get();

        return view('dashboard', [
            'activeSession' => $activeSession,
            'activeReservation' => $activeReservation,
            'stats' => $stats,
            'recent' => $recent,
            'vehicleCount' => $user->vehicles()->count(),
            'outstanding' => $user->outstandingAmount(),
        ]);
    }
}