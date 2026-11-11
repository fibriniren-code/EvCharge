<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SPKLUController extends Controller
{
    private const STATUS_LOKASI_AKTIF = 'aktif';

    private const RADIUS_DEFAULT_KM = 50;

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
                    },
                ]);
            },
        ])->where('status', self::STATUS_LOKASI_AKTIF);

        if ($request->filled('search')) {
            $this->applySearch($query, trim($request->search));
        }

        $locations = $query
            ->latest('id_location')
            ->paginate(9)
            ->withQueryString();

        return view('spklu.index', compact('locations'));
    }

    /**
     * Menampilkan detail lokasi SPKLU beserta daftar charger.
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
                    },
                ]);
            },
        ])->findOrFail($id);

        return view('spklu.show', [
            'location' => $location,
            'tariff'   => $location->currentTariff(),
        ]);
    }

    /**
     * API: daftar lokasi (JSON) untuk halaman peta.
     * Jika latitude & longitude dikirim, hasil diurutkan dari yang terdekat.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'latitude'  => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'radius'    => ['nullable', 'numeric', 'min:1', 'max:500'],
            'search'    => ['nullable', 'string', 'max:100'],
        ]);

        return $this->locationsResponse($filters);
    }

    /**
     * API: lokasi terdekat dari koordinat pengemudi (FR-02).
     */
    public function nearby(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius'    => ['nullable', 'numeric', 'min:1', 'max:500'],
        ]);

        $filters['radius'] = $filters['radius'] ?? self::RADIUS_DEFAULT_KM;

        return $this->locationsResponse($filters);
    }

    private function locationsResponse(array $filters): JsonResponse
    {
        $query = Location::with('chargers')
            ->where('status', self::STATUS_LOKASI_AKTIF);

        if (! empty($filters['search'])) {
            $this->applySearch($query, trim($filters['search']));
        }

        $locations = $query->get();

        if (isset($filters['latitude'], $filters['longitude'])) {
            $locations = $this->withDistance(
                $locations,
                (float) $filters['latitude'],
                (float) $filters['longitude'],
                isset($filters['radius']) ? (float) $filters['radius'] : null
            );
        }

        return response()->json([
            'status' => 'success',
            'data'   => $locations->values(),
        ]);
    }

    /**
     * Pencarian berdasarkan nama lokasi, alamat, atau jenis charger.
     */
    private function applySearch($query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('nama_lokasi', 'like', '%' . $search . '%')
              ->orWhere('alamat', 'like', '%' . $search . '%')
              ->orWhereHas('chargers', function ($chargerQuery) use ($search) {
                  $chargerQuery->where('charger_type', 'like', '%' . $search . '%');
              });
        });
    }

    /**
     * Menambahkan atribut `distance` (km), menyaring berdasarkan radius
     * bila diberikan, lalu mengurutkan dari yang terdekat.
     */
    private function withDistance(Collection $locations, float $lat, float $lng, ?float $radiusKm): Collection
    {
        $locations = $locations->map(function (Location $location) use ($lat, $lng) {
            $location->setAttribute(
                'distance',
                round($this->haversineKm($lat, $lng, $location->latitude, $location->longitude), 2)
            );

            return $location;
        });

        if ($radiusKm !== null) {
            $locations = $locations->filter(fn (Location $location) => $location->distance <= $radiusKm);
        }

        return $locations->sortBy('distance');
    }

    /**
     * Jarak antara dua koordinat (kilometer) dengan Haversine Formula.
     */
    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
