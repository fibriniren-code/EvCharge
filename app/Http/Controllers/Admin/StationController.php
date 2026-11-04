<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location; // atau Station
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function index()
    {
        $stations = Location::with('chargers')->paginate(10);
        return view('admin.stations.index', compact('stations'));
    }

    public function create()
    {
        return view('admin.stations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'address'   => 'required|string',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        Location::create($request->all());

        return redirect()->route('admin.stations.index')->with('success', 'Stasiun berhasil ditambahkan.');
    }

    // Toggle Buka/Tutup Sementara Stasiun (FR-10)
    public function toggleStatus($id)
    {
        $station = Location::findOrFail($id);
        $station->is_active = !$station->is_active;
        $station->save();

        return back()->with('success', 'Status operasional stasiun berhasil diperbarui.');
    }
}