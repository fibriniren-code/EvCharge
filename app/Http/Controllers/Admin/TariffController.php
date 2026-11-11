<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    // Menampilkan tarif per stasiun (FR-15)
    public function index()
    {
        $tariffs = Tariff::with('location')
            ->orderBy('id_location')
            ->get();

        return view('admin.tariffs.index', compact('tariffs'));
    }

    // Memperbarui tarif per kWh, biaya layanan, dan overstay fee
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'harga_per_kwh'          => 'required|numeric|min:0',
            'biaya_layanan'          => 'required|numeric|min:0',
            'overstay_fee_per_menit' => 'required|numeric|min:0',
        ]);

        $tariff = Tariff::findOrFail($id);
        $tariff->update($validated);

        return back()->with('success', 'Tarif operasional berhasil diperbarui.');
    }
}
