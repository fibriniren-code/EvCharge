<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SPKLUController extends Controller
{
    /**
     * Menampilkan daftar SPKLU dengan pencarian
     * dan status ketersediaan charger.
     */
    public function index(Request $request)
    {
        $query = Location::with([
            'chargers' => function ($query) {
                $query->withCount([
                    'reservations as active_reservations_count' => function ($q) {
                        $q->whereIn('status', ['pending', 'confirmed'])
                          ->where('expires_at', '>', now());
                    },

                    'chargingSessions as active_sessions_count' => function ($q) {
                        $q->where('status', 'running');
                    }
                ]);
            }
        ]);

        // Pencarian lokasi dan jenis charger
        if ($request->filled('search')) {
            $search = trim($request->search);

            $chargerColumns = Schema::getColumnListing('chargers');

            $chargerTypeColumn = null;

            if (in_array('type', $chargerColumns)) {
                $chargerTypeColumn = 'type';
            }

            $query->where(function ($q) use ($search, $chargerTypeColumn) {
                $q->where('nama_lokasi', 'like', '%' . $search . '%')
                  ->orWhere('alamat', 'like', '%' . $search . '%');

                if ($chargerTypeColumn) {
                    $q->orWhereHas('chargers', function ($chargerQuery) use ($search, $chargerTypeColumn) {
                        $chargerQuery->where(
                            $chargerTypeColumn,
                            'like',
                            '%' . $search . '%'
                        );
                    });
                }
            });
        }

        $locations = $query
            ->latest('id_location')
            ->paginate(9)
            ->withQueryString();

        return view('spklu.index', compact('locations'));
    }

    /**
     * Menampilkan detail lokasi SPKLU
     * beserta daftar charger.
     */
    public function show($id)
    {
        $location = Location::with([
            'chargers' => function ($query) {
                $query->withCount([
                    'reservations as active_reservations_count' => function ($q) {
                        $q->whereIn('status', ['pending', 'confirmed'])
                          ->where('expires_at', '>', now());
                    },

                    'chargingSessions as active_sessions_count' => function ($q) {
                        $q->where('status', 'running');
                    }
                ]);
            }
        ])->findOrFail($id);

        return view('spklu.show', compact('location'));
    }
}
