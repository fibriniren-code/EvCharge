<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::with('chargers'); // Wajib sertakan relasi chargers

        // Pencarian berdasarkan kata kunci
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%');
        }

        // Perhitungan Jarak Haversine (Jika ada koordinat latitude & longitude)
        if ($request->has('latitude') && $request->has('longitude')) {
            $lat = $request->latitude;
            $lng = $request->longitude;

            $query->selectRaw("*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance", [$lat, $lng, $lat]);
            
            // Urutkan dari yang terdekat
            $query->orderBy('distance', 'asc');
        }

        $locations = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $locations
        ]);
    }
}