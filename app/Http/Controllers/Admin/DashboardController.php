<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Charger;
use App\Models\ChargingSession;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Menghitung statistik status charger secara real-time
        $statusCounts = [
            'available'   => Charger::where('status', 'available')->count(),
            'occupied'    => Charger::where('status', 'occupied')->count(),
            'faulted'     => Charger::where('status', 'faulted')->count(),
            'unavailable' => Charger::where('status', 'unavailable')->count(),
            'total'       => Charger::count(),
        ];

        // 2. Sesi pengisian daya yang sedang aktif (lengkap dengan data user & charger)
        $activeSessions = ChargingSession::with(['user', 'charger'])
            ->where('status', 'active')
            ->latest()
            ->get();

        // 3. Daftar charger yang mengalami kendala teknis (FR-09 Alert System)
        $faultedChargers = Charger::where('status', 'faulted')->get();

        // 4. Return ke view admin dengan variabel data yang siap dipakai
        return view('admin.dashboard', compact(
            'statusCounts',
            'activeSessions',
            'faultedChargers'
        ));
    }
}