<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SPKLUController extends Controller
{
    private const STATUS_LOKASI_AKTIF = 'aktif';

    private const RADIUS_DEFAULT_KM = 50;

    /**
     * Daftar + peta SPKLU. Semua stasiun dimuat sekaligus; pencarian dan filter
     * dilakukan di sisi klien agar peta dan daftar selalu sinkron.
     */
    public function index(Request $request)
    {
        Reservation::expireStale();

        $locations = Location::with(['chargers', 'tariffs'])
            ->orderBy('nama_lokasi')
            ->limit(300)
            ->get();

        return view('spklu.index', [
            'locations' => $locations,
            'search' => trim((string) $request->query('search', '')),
        ]);
    }

    public function show($id)
    {
        Reservation::expireStale();

        $location = Location::with('chargers.runningSession')->findOrFail($id);

        $reviews = Review::with('user')
            ->where('id_location', $location->id_location)
            ->latest('id_review')
            ->take(5)
            ->get();

        return view('spklu.show', [
            'location' => $location,
            'tariff' => $location->currentTariff(),
            'reviews' => $reviews,
            'avgAlat' => (float) Review::where('id_location', $location->id_location)->avg('rating_alat'),
            'avgTempat' => (float) Review::where('id_location', $location->id_location)->avg('rating_tempat'),
            'reviewCount' => Review::where('id_location', $location->id_location)->count(),
        ]);
    }

    /** API: daftar lokasi (JSON). Bila koordinat dikirim, diurutkan dari yang terdekat. */
    public function apiIndex(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'radius' => ['nullable', 'numeric', 'min:1', 'max:500'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        return $this->locationsResponse($filters);
    }

    /** API: lokasi terdekat dari koordinat pengemudi. */
    public function nearby(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:1', 'max:500'],
        ]);

        $filters['radius'] = $filters['radius'] ?? self::RADIUS_DEFAULT_KM;

        return $this->locationsResponse($filters);
    }

    private function locationsResponse(array $filters): JsonResponse
    {
        $query = Location::with('chargers')->where('status', self::STATUS_LOKASI_AKTIF);

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('nama_lokasi', 'like', '%' . $search . '%')
                    ->orWhere('alamat', 'like', '%' . $search . '%')
                    ->orWhereHas('chargers', fn ($c) => $c->where('charger_type', 'like', '%' . $search . '%'));
            });
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

        return response()->json(['status' => 'success', 'data' => $locations->values()]);
    }

    private function withDistance(Collection $locations, float $lat, float $lng, ?float $radiusKm): Collection
    {
        $locations = $locations->map(function (Location $location) use ($lat, $lng) {
            $location->setAttribute('distance', round($this->haversineKm($lat, $lng, $location->latitude, $location->longitude), 2));

            return $location;
        });

        if ($radiusKm !== null) {
            $locations = $locations->filter(fn (Location $location) => $location->distance <= $radiusKm);
        }

        return $locations->sortBy('distance');
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}