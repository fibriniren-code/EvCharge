<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function searchNearby(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric|default:50', // km
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;

        // Kalkulasi Haversine Formula[cite: 3]
        $locations = Location::selectRaw("
            *, ( 6371 * acos( cos( radians(?) ) 
            * cos( radians( latitude ) ) 
            * cos( radians( longitude ) - radians(?) ) 
            + sin( radians(?) ) 
            * sin( radians( latitude ) ) ) ) AS distance", [$lat, $lng, $lat])
            ->with(['chargers', 'tariffs'])
            ->having('distance', '<=', $request->radius ?? 50)
            ->orderBy('distance', 'asc') // Urutkan dari yang terdekat[cite: 3]
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $locations
        ]);
    }
}