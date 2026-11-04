<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Charger;
use App\Models\ChargingSession;
use App\Models\Reservation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Hitung statistik status charger secara real-time
        $statusCounts = [
            'available'   => Charger::where('status', 'available')->count(),
            'occupied'    => Charger::where('status', 'occupied')->count(),
            'faulted'     => Charger::where('status', 'faulted')->count(),
            'unavailable' => Charger::where('status', 'unavailable')->count(),
            'total'       => Charger::count(),
        ];

        // 2. Ambil Sesi Pengisian Daya yang SEDANG AKTIF / BERJALAN
        $activeSessions = ChargingSession::with(['user', 'charger'])
            ->whereNotIn('status', ['completed', 'stopped', 'finished', 'done'])
            ->latest()
            ->get();

        // 3. Ambil Daftar Reservasi / Booking untuk Monitoring
        $activeReservations = Reservation::with(['user', 'charger'])
            ->latest('id_reservation')
            ->take(15)
            ->get();

        // 4. Charger yang mengalami kendala teknis
        $faultedChargers = Charger::where('status', 'faulted')->get();

        // 5. TAMBAHAN: Ambil Riwayat Pengisian Daya yang SUDAH SELESAI
        $completedSessions = ChargingSession::with(['user', 'charger'])
            ->whereIn('status', ['completed', 'stopped', 'finished', 'done'])
            ->latest()
            ->take(10)
            ->get();

        // Fallback: Jika tabel ChargingSession belum ada data completed, ambil dari model Reservation
        if ($completedSessions->isEmpty()) {
            $completedSessions = Reservation::with(['user', 'charger'])
                ->whereIn('status', ['completed', 'finished'])
                ->latest('id_reservation')
                ->take(10)
                ->get();
        }

        return view('admin.dashboard', compact(
            'statusCounts',
            'activeSessions',
            'activeReservations',
            'faultedChargers',
            'completedSessions'
        ));
    }
}