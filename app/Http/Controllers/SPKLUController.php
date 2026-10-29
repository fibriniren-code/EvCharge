<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SPKLUController extends Controller
{
    /**
     * Menampilkan daftar SPKLU dengan pencarian & status ketersediaan charger real-time (FR 1)
     */
    public function index(Request $request)
    {
        $query = Location::with(['chargers' => function ($query) {
            $query->withCount([
                'reservations as active_reservations_count' => function ($q) {
                    $q->whereIn('status', ['pending', 'active'])
                      ->where('expires_at', '>', now());
                },
                'chargingSessions as active_sessions_count' => function ($q) {
                    $q->where('status', 'active');
                }
            ]);
        }]);

        // Filter kata kunci pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);

            // Ambil seluruh daftar kolom yang ada di tabel 'chargers'
            $chargerColumns = Schema::getColumnListing('chargers');

            // Tentukan kolom mana yang tersedia untuk pencarian jenis charger
            $chargerTypeColumn = null;
            if (in_array('charger_type', $chargerColumns)) {
                $chargerTypeColumn = 'charger_type';
            } elseif (in_array('tipe_charger', $chargerColumns)) {
                $chargerTypeColumn = 'tipe_charger';
            }

            $query->where(function ($q) use ($search, $chargerTypeColumn) {
                $q->where('nama_lokasi', 'like', '%' . $search . '%')
                  ->orWhere('alamat', 'like', '%' . $search . '%');

                // Hanya tambahkan pencarian relasi charger jika kolom ketersediaan jenis charger ditemukan
                if ($chargerTypeColumn) {
                    $q->orWhereHas('chargers', function ($chargerQuery) use ($search, $chargerTypeColumn) {
                        $chargerQuery->where($chargerTypeColumn, 'like', '%' . $search . '%');
                    });
                }
            });
        }

        $locations = $query->latest('id_location')
                           ->paginate(9)
                           ->withQueryString();

        return view('spklu.index', compact('locations'));
    }

    /**
     * Menampilkan detail lokasi SPKLU beserta daftar charger yang tersedia
     */
    public function show($id)
    {
        $location = Location::with(['chargers' => function ($query) {
            $query->withCount([
                'reservations as active_reservations_count' => function ($q) {
                    $q->whereIn('status', ['pending', 'active'])
                      ->where('expires_at', '>', now());
                },
                'chargingSessions as active_sessions_count' => function ($q) {
                    $q->where('status', 'active');
                }
            ]);
        }])->findOrFail($id);

        return view('spklu.show', compact('location'));
    }
}