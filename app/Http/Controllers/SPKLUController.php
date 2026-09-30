<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Charger;
use Illuminate\Http\Request;

class SPKLUController extends Controller
{
    public function index(Request $request)
    {
        // Jika pencarian terisi, ambil data lokasi sesuai keyword
        if ($request->filled('search')) {
            $search = $request->search;

            $locations = Location::with('chargers')
                ->where(function ($q) use ($search) {
                    $q->where('nama_lokasi', 'like', '%' . $search . '%')
                      ->orWhere('alamat', 'like', '%' . $search . '%');
                })
                ->latest()
                ->paginate(9)
                ->withQueryString();
        } else {
            // Jika belum ada pencarian, kembalikan collection kosong
            $locations = collect();
        }

        return view('spklu.index', [
            'locations' => $locations,
        ]);
    }
}