<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Charger;
use App\Models\ChargingSession;
use App\Models\Location;
use App\Models\Reservation;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StationController extends Controller
{
    public const CHARGER_TYPES = [
        'CCS2 (Fast Charging)',
        'CCS2 Ultra Fast Charging',
        'Type 2 (AC)',
        'CCS2 + CHAdeMO',
        'CHAdeMO',
        'GB/T',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $stations = Location::with(['chargers', 'tariffs'])
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nama_lokasi', 'like', "%{$search}%")
                ->orWhere('alamat', 'like', "%{$search}%")))
            ->orderBy('nama_lokasi')
            ->paginate(10)
            ->withQueryString();

        return view('admin.stations.index', compact('stations', 'search'));
    }

    public function create()
    {
        return view('admin.stations.create');
    }

    private function locationRules(): array
    {
        return [
            'nama_lokasi' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:500'],
            'detail_area' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'jam_operasional' => ['nullable', 'string', 'max:100'],
            'fasilitas' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:aktif,tutup'],
        ];
    }

    private function locationData(array $validated): array
    {
        // Fasilitas diinput dipisah koma, disimpan sebagai string JSON (sama seperti seeder)
        $facilities = array_values(array_filter(array_map('trim', explode(',', (string) ($validated['fasilitas'] ?? '')))));

        return [
            'nama_lokasi' => $validated['nama_lokasi'],
            'alamat' => $validated['alamat'],
            'detail_area' => $validated['detail_area'] ?? '',
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'jam_operasional' => $validated['jam_operasional'] ?? null,
            'fasilitas' => json_encode($facilities),
            'status' => $validated['status'],
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->locationRules() + [
            'harga_per_kwh' => ['required', 'numeric', 'min:0'],
            'biaya_layanan' => ['nullable', 'numeric', 'min:0'],
            'biaya_minimum' => ['nullable', 'numeric', 'min:0'],
            'overstay_fee_per_menit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $station = DB::transaction(function () use ($validated) {
            $station = Location::create($this->locationData($validated));

            // Setiap stasiun wajib punya tarif agar perhitungan biaya berjalan
            Tariff::create([
                'id_location' => $station->id_location,
                'harga_per_kwh' => $validated['harga_per_kwh'],
                'biaya_layanan' => $validated['biaya_layanan'] ?? 0,
                'biaya_minimum' => $validated['biaya_minimum'] ?? 0,
                'overstay_fee_per_menit' => $validated['overstay_fee_per_menit'] ?? 0,
                'periode_berlaku' => now(),
            ]);

            return $station;
        });

        return redirect()->route('admin.stations.edit', $station)
            ->with('success', 'Stasiun dibuat. Tambahkan charger pada halaman ini.');
    }

    public function edit(Location $station)
    {
        $station->load('chargers.runningSession');

        return view('admin.stations.edit', [
            'station' => $station,
            'chargerTypes' => self::CHARGER_TYPES,
        ]);
    }

    public function update(Request $request, Location $station)
    {
        $station->update($this->locationData($request->validate($this->locationRules())));

        return back()->with('success', 'Data stasiun berhasil diperbarui.');
    }

    public function destroy(Location $station)
    {
        $ids = $station->chargers()->pluck('id_charger');

        $busy = ChargingSession::running()->whereIn('id_charger', $ids)->exists()
            || Reservation::holding()->whereIn('charger_id', $ids)->exists();

        if ($busy) {
            return back()->withErrors(['error' => 'Stasiun masih memiliki sesi charging atau reservasi aktif.']);
        }

        $station->delete();

        return redirect()->route('admin.stations.index')->with('success', 'Stasiun berhasil dihapus.');
    }

    // Buka/tutup sementara stasiun
    public function toggleStatus(Location $station)
    {
        if ($station->isOpen()) {
            $cancelled = $station->close();

            return back()->with('success', 'Stasiun ditutup sementara.' . ($cancelled ? " {$cancelled} reservasi aktif dibatalkan." : ''));
        }

        $station->open();

        return back()->with('success', 'Stasiun dibuka kembali.');
    }

    public function storeCharger(Request $request, Location $station)
    {
        $data = $request->validate([
            'charger_type' => ['required', 'string', 'max:100'],
            'power_kw' => ['required', 'numeric', 'min:1', 'max:500'],
        ]);

        $station->chargers()->create($data + ['status' => 'available']);

        return back()->with('success', 'Charger berhasil ditambahkan.');
    }

    public function destroyCharger(Charger $charger)
    {
        if ($charger->status === 'occupied' || $charger->activeHolds()->exists()) {
            return back()->withErrors(['error' => 'Charger sedang dipakai atau ditahan reservasi.']);
        }

        $charger->delete();

        return back()->with('success', 'Charger berhasil dihapus.');
    }
}