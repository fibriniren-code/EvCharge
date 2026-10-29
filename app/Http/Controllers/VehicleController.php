<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    // Tampilkan Daftar Mobil
    public function index()
    {
        $vehicles = auth()->user()->vehicles ?? collect();
        return view('driver.vehicles.index', compact('vehicles'));
    }

    // Tampilkan Form Tambah Mobil
    public function create()
    {
        return view('driver.vehicles.create');
    }

    // Simpan Mobil Baru
    public function store(Request $request)
    {
        $request->validate([
            'merek'                 => 'required|string|max:100',
            'model'                 => 'required|string|max:100',
            'nomor_polisi'          => 'required|string|max:20|unique:vehicles,nomor_polisi',
            'kapasitas_baterai_kwh' => 'required|numeric|min:1',
            'tipe_konektor'         => 'required|string',
        ]);

        Vehicle::create([
            'user_id'               => auth()->id(),
            'merek'                 => $request->merek,
            'model'                 => $request->model,
            'nomor_polisi'          => strtoupper($request->nomor_polisi),
            'kapasitas_baterai_kwh' => $request->kapasitas_baterai_kwh,
            'tipe_konektor'         => $request->tipe_konektor,
        ]);

        return redirect()->route('cars.index')->with('success', 'Mobil listrik berhasil ditambahkan!');
    }

    // Hapus Mobil
    public function destroy($id)
    {
        $vehicle = Vehicle::where('user_id', auth()->id())->findOrFail($id);
        $vehicle->delete();

        return redirect()->route('cars.index')->with('success', 'Mobil listrik berhasil dihapus!');
    }
}