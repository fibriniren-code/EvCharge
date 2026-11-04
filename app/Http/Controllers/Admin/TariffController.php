<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index()
    {
        $tariffs = Tariff::all();
        return view('admin.tariffs.index', compact('tariffs'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'price_per_kwh'        => 'required|numeric|min:0',
            'service_fee'          => 'required|numeric|min:0',
            'overstay_fee_per_min' => 'required|numeric|min:0',
        ]);

        $tariff = Tariff::findOrFail($id);
        $tariff->update([
            'price_per_kwh'        => $request->price_per_kwh,
            'service_fee'          => $request->service_fee,
            'overstay_fee_per_min' => $request->overstay_fee_per_min,
        ]);

        return back()->with('success', 'Tarif operasional berhasil diperbarui.');
    }
}