<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Tariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    // Tarif berlaku per stasiun, termasuk stasiun yang belum punya tarif
    public function index()
    {
        $stations = Location::with('tariffs')->orderBy('nama_lokasi')->get()
            ->map(function (Location $location) {
                $location->setRelation('activeTariff', $location->currentTariff());

                return $location;
            });

        return view('admin.tariffs.index', compact('stations'));
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'harga_per_kwh' => ['required', 'numeric', 'min:0'],
            'biaya_layanan' => ['required', 'numeric', 'min:0'],
            'biaya_minimum' => ['required', 'numeric', 'min:0'],
            'overstay_fee_per_menit' => ['required', 'numeric', 'min:0'],
        ]);

        $tariff = $location->currentTariff();

        if ($tariff) {
            $tariff->update($validated);
        } else {
            Tariff::create($validated + [
                'id_location' => $location->id_location,
                'periode_berlaku' => now(),
            ]);
        }

        return back()->with('success', 'Tarif ' . $location->nama_lokasi . ' berhasil diperbarui. Berlaku untuk sesi berikutnya.');
    }
}