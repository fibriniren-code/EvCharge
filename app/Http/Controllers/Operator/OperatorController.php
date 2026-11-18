<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Charger;
use App\Models\ChargingSession;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    /**
     * Tampilkan Dashboard Khusus Operator SPKLU
     */
    public function index()
    {
        $user = auth()->user();

        // 1. Ambil daftar lokasi SPKLU (bisa difilter per operator jika ada user_id di lokasi)
        $locations = Location::with('chargers')->latest()->get();

        // 2. Hitung total statistik
        $totalStations = $locations->count();
        $totalChargers = Charger::count();
        $activeSessions = ChargingSession::whereNotIn('status', ['completed', 'stopped', 'finished', 'done'])->count();
        $faultedChargers = Charger::where('status', 'faulted')->count();

        // 3. Ambil data sesi charging real-time
        $currentSessions = ChargingSession::with(['user', 'charger'])
            ->whereNotIn('status', ['completed', 'stopped', 'finished', 'done'])
            ->latest()
            ->take(10)
            ->get();

        return view('operator.dashboard', compact(
            'locations',
            'totalStations',
            'totalChargers',
            'activeSessions',
            'faultedChargers',
            'currentSessions'
        ));
    }

    /**
     * Form Tambah Stasiun / Lokasi SPKLU Baru
     */
    public function createStation()
    {
        return view('operator.stations.create');
    }

    /**
     * Simpan Stasiun SPKLU Baru ke Database
     */
    public function storeStation(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'required|string',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'city'        => 'required|string|max:100',
            'total_slots' => 'required|integer|min:1',
        ]);

        $location = Location::create([
            'name'        => $validated['name'],
            'address'     => $validated['address'],
            'latitude'    => $validated['latitude'],
            'longitude'   => $validated['longitude'],
            'city'        => $validated['city'],
            'status'      => 'active',
        ]);

        // Otomatis buatkan charger slots sesuai jumlah slot
        for ($i = 1; $i <= $validated['total_slots']; $i++) {
            Charger::create([
                'location_id'  => $location->id,
                'charger_code' => 'CHG-' . $location->id . '-' . sprintf('%02d', $i),
                'connector_type' => 'CCS2',
                'max_power_kw' => 50,
                'status'       => 'available',
            ]);
        }

        return redirect()->route('operator.dashboard')->with('success', 'Stasiun SPKLU berhasil ditambahkan!');
    }
}