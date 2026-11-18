<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public const CONNECTORS = ['CCS2 (Fast Charging)', 'Type 2 (AC)', 'CHAdeMO', 'GB/T'];

    public function index()
    {
        return view('driver.vehicles.index', [
            'vehicles' => auth()->user()->vehicles()->latest('id_vehicle')->get(),
            'connectors' => self::CONNECTORS,
        ]);
    }

    public function create()
    {
        return view('driver.vehicles.create', ['connectors' => self::CONNECTORS]);
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'merek' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'nomor_polisi' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'nomor_polisi')->ignore($ignoreId, 'id_vehicle')],
            'kapasitas_baterai_kwh' => ['required', 'numeric', 'min:1', 'max:500'],
            'tipe_konektor' => ['required', Rule::in(self::CONNECTORS)],
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['nomor_polisi'] = strtoupper($data['nomor_polisi']);

        Vehicle::create($data + ['user_id' => auth()->id()]);

        return redirect()->route('cars.index')->with('success', 'Mobil listrik berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::where('user_id', auth()->id())->findOrFail($id);

        $data = $request->validate($this->rules($vehicle->id_vehicle));
        $data['nomor_polisi'] = strtoupper($data['nomor_polisi']);

        $vehicle->update($data);

        return redirect()->route('cars.index')->with('success', 'Data kendaraan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Vehicle::where('user_id', auth()->id())->findOrFail($id)->delete();

        return redirect()->route('cars.index')->with('success', 'Mobil listrik berhasil dihapus!');
    }
}