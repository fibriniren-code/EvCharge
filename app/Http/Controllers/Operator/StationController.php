<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Charger;
use App\Models\Location;
use App\Models\Reservation;
use Illuminate\Http\Request;

class StationController extends Controller
{
    /** Kendali stasiun & port (FR-10). */
    public function index()
    {
        Reservation::expireStale();

        return view('operator.stations', [
            'stations' => Location::with(['chargers.runningSession.user', 'tariffs'])
                ->orderBy('nama_lokasi')
                ->get(),
        ]);
    }

    /** Buka/tutup stasiun. Menutup membatalkan reservasi aktif; sesi yang berjalan tidak terganggu. */
    public function toggle(Location $station)
    {
        if ($station->isOpen()) {
            $cancelled = $station->close();

            return back()->with(
                'success',
                "{$station->nama_lokasi} ditutup sementara." . ($cancelled ? " {$cancelled} reservasi aktif dibatalkan." : '')
            );
        }

        $station->open();

        return back()->with('success', "{$station->nama_lokasi} dibuka kembali.");
    }

    /** Ubah status port: available | unavailable | faulted. */
    public function chargerStatus(Request $request, Charger $charger)
    {
        $data = $request->validate([
            'status' => ['required', 'in:available,unavailable,faulted'],
        ]);

        [$ok, $message] = $charger->setOperationalStatus($data['status']);

        return back()->with($ok ? 'success' : 'error', $message);
    }
}