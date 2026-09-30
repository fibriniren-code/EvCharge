<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Auth::user()->vehicles;

        return view('vehicles.index', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'merek'                 => 'required|string|max:100',
            'model'                 => 'required|string|max:100',
            'nomor_polisi'          => 'required|string|max:20|unique:vehicles,nomor_polisi',
            'kapasitas_baterai_kwh' => 'required|numeric|min:1',
            'tipe_konektor'        => 'required|string|max:50',
        ]);

        $request->user()->vehicles()->create($validated);

        return redirect()->back()->with('status', 'vehicle-added');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        if ($vehicle->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'merek'                 => 'required|string|max:100',
            'model'                 => 'required|string|max:100',
            'nomor_polisi'          => 'required|string|max:20|unique:vehicles,nomor_polisi,' . $vehicle->id_vehicle . ',id_vehicle',
            'kapasitas_baterai_kwh' => 'required|numeric|min:1',
            'tipe_konektor'        => 'required|string|max:50',
        ]);

        $vehicle->update($validated);

        return redirect()->back()->with('status', 'vehicle-updated');
    }

    public function destroy(Vehicle $vehicle)
    {
        if ($vehicle->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $vehicle->delete();

        return redirect()->back()->with('status', 'vehicle-deleted');
    }
}