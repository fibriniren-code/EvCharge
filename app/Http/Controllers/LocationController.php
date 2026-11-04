<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::all();

        return view('locations.index', compact('locations'));
    }

    public function show($id)
    {
        $location = Location::with('chargers')
            ->where('id_location', $id)
            ->firstOrFail();

        $chargers = $location->chargers;

        return view('driver.location.show', compact('location', 'chargers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'alamat' => 'required|string',
            'detail_area' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        Location::create($validated);

        return redirect()->back()->with(
            'success',
            'Lokasi SPKLU berhasil ditambahkan!'
        );
    }
}
