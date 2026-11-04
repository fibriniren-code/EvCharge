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
        // Menghitung status port/charger secara real-time
        $statusCounts = [
            'available'   => Charger::where('status', 'available')->count(),
            'occupied'    => Charger::where('status', 'occupied')->count(),
            'faulted'     => Charger::where('status', 'faulted')->count(),
            'unavailable' => Charger::where('status', 'unavailable')->count(),
        ];

        // Sesi pengisian daya yang sedang aktif
        $activeSessions = ChargingSession::with(['user', 'charger'])
            ->where('status', 'active')
            ->latest()
            ->get();

        // Daftar charger yang mengalami kendala teknis (FR-09 Alert)
        $faultedChargers = Charger::where('status', 'faulted')->get();

        return view('admin.dashboard', compact('statusCounts', 'activeSessions', 'faultedChargers'));
    }
}