<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Charger;
use App\Models\ChargingSession;
use App\Models\Location;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        Reservation::expireStale();

        $finished = ['completed', 'interrupted'];

        $today = ChargingSession::whereIn('status', $finished)
            ->where('waktu_selesai', '>=', now()->startOfDay());

        $stats = [
            'revenue_today' => (float) (clone $today)->sum('estimasi_biaya'),
            'kwh_today' => (float) (clone $today)->sum('energi_kwh'),
            'sessions_today' => (clone $today)->count(),
            'active_sessions' => ChargingSession::running()->count(),
            'active_reservations' => Reservation::holding()->count(),
            'stations' => Location::count(),
            'stations_open' => Location::where('status', 'aktif')->count(),
            'drivers' => User::where('role', 'driver')->count(),
        ];

        $raw = Charger::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $statusCounts = [
            'available' => (int) ($raw['available'] ?? 0),
            'reserved' => (int) ($raw['reserved'] ?? 0),
            'occupied' => (int) ($raw['occupied'] ?? 0),
            'faulted' => (int) ($raw['faulted'] ?? 0),
            'unavailable' => (int) ($raw['unavailable'] ?? 0),
        ];
        $statusCounts['total'] = array_sum($statusCounts);

        // Pendapatan 7 hari terakhir
        $days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $rows = ChargingSession::whereIn('status', $finished)
            ->where('waktu_selesai', '>=', now()->subDays(6)->startOfDay())
            ->get(['waktu_selesai', 'estimasi_biaya'])
            ->groupBy(fn ($s) => $s->waktu_selesai->format('Y-m-d'));

        $revenue7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $revenue7[] = [
                'label' => $days[$d->dayOfWeek],
                'value' => (float) ($rows[$d->format('Y-m-d')] ?? collect())->sum('estimasi_biaya'),
            ];
        }

        return view('admin.dashboard', [
            'stats' => $stats,
            'statusCounts' => $statusCounts,
            'revenue7' => $revenue7,
            'activeSessions' => ChargingSession::with(['user', 'charger.location'])->running()->latest('id_session')->get(),
            'recentSessions' => ChargingSession::with(['user', 'charger.location'])->whereIn('status', $finished)->latest('id_session')->take(8)->get(),
            'faultedChargers' => Charger::with('location')->where('status', 'faulted')->get(),
            'reservations' => Reservation::with(['user', 'charger.location'])->latest('id_reservation')->take(8)->get(),
            'avgAlat' => (float) Review::avg('rating_alat'),
            'avgTempat' => (float) Review::avg('rating_tempat'),
            'unreplied' => Review::whereNull('balasan_operator')->count(),
        ]);
    }
}