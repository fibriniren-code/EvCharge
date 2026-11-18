<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\ChargingSession;

class SessionController extends Controller
{
    public function index()
    {
        return view('operator.sessions', [
            'running' => ChargingSession::with(['user', 'charger.location', 'reservation.vehicle'])
                ->running()
                ->latest('id_session')
                ->get(),
            'recent' => ChargingSession::with(['user', 'charger.location'])
                ->whereIn('status', ['completed', 'interrupted'])
                ->latest('id_session')
                ->take(12)
                ->get(),
        ]);
    }

    /** Henti darurat: sesi ditutup, pengemudi hanya ditagih sebatas energi yang sudah tersalurkan. */
    public function stop(ChargingSession $session)
    {
        $result = $session->settle('interrupted');

        if ($result === null) {
            return back()->with('info', 'Sesi ini sudah tidak berjalan.');
        }

        return back()->with(
            'success',
            'Sesi #' . $session->id_session . ' dihentikan. Energi tersalurkan ' . number_format($result['kwh'], 2, ',', '.') . ' kWh.'
        );
    }
}