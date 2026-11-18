<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Charger;
use App\Models\ChargingSession;
use App\Models\Location;
use App\Models\Reservation;
use App\Models\Review;

class DashboardController extends Controller
{
    /** Monitoring real-time (FR-09). Halaman disegarkan otomatis lewat region [data-live] di view. */
    public function index()
    {
        Reservation::expireStale();

        $stations = Location::with(['chargers.runningSession.user'])->orderBy('nama_lokasi')->get();

        $raw = Charger::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $counts = [];
        foreach (['available', 'reserved', 'occupied', 'faulted', 'unavailable'] as $s) {
            $counts[$s] = (int) ($raw[$s] ?? 0);
        }
        $counts['total'] = array_sum($counts);

        $running = ChargingSession::with(['user', 'charger.location', 'reservation'])
            ->running()
            ->latest('id_session')
            ->get();

        $overstay = $running->filter(fn ($s) => $s->reservation && $s->reservation->waktu_selesai->isPast());

        // Keluhan = ulasan tanpa balasan dengan rating alat atau tempat <= 2
        $complaints = Review::with(['user', 'location'])
            ->whereNull('balasan_operator')
            ->where(fn ($q) => $q->where('rating_alat', '<=', 2)->orWhere('rating_tempat', '<=', 2))
            ->latest('id_review')
            ->take(4)
            ->get();

        $alerts = [];

        foreach (Charger::with('location')->where('status', 'faulted')->get() as $c) {
            $alerts[] = [
                'tone' => 'rose',
                'icon' => 'alert',
                'title' => "Charger #{$c->id_charger} rusak",
                'text' => $c->location?->nama_lokasi,
                'url' => route('operator.stations.index') . '#charger-' . $c->id_charger,
            ];
        }

        foreach ($overstay as $s) {
            $minutes = (int) ceil($s->reservation->waktu_selesai->diffInSeconds(now(), true) / 60);
            $alerts[] = [
                'tone' => 'amber',
                'icon' => 'clock',
                'title' => "Overstay {$minutes} menit",
                'text' => ($s->user->name ?? 'Pengemudi') . ' · Charger #' . $s->id_charger,
                'url' => route('operator.sessions.index'),
            ];
        }

        foreach ($stations->where('status', '!=', 'aktif') as $st) {
            $alerts[] = [
                'tone' => 'slate',
                'icon' => 'power',
                'title' => 'Stasiun ditutup',
                'text' => $st->nama_lokasi,
                'url' => route('operator.stations.index') . '#station-' . $st->id_location,
            ];
        }

        if ($complaints->isNotEmpty()) {
            $alerts[] = [
                'tone' => 'violet',
                'icon' => 'chat',
                'title' => $complaints->count() . ' keluhan belum dibalas',
                'text' => 'Rating rendah dari pengemudi',
                'url' => route('operator.reviews.index', ['filter' => 'complaints']),
            ];
        }

        $todayDone = ChargingSession::whereIn('status', ['completed', 'interrupted'])
            ->where('waktu_selesai', '>=', now()->startOfDay());

        return view('operator.dashboard', [
            'stations' => $stations,
            'counts' => $counts,
            'running' => $running,
            'alerts' => $alerts,
            'complaints' => $complaints,
            'today' => [
                'sessions' => (clone $todayDone)->count(),
                'kwh' => (float) (clone $todayDone)->sum('energi_kwh'),
            ],
            'now' => now(),
        ]);
    }
}