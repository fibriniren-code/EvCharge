<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\Charger;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'nama_lokasi' => 'SPKLU PLN UID Jaya Gambir',
                'alamat' => 'Jl. M.I. Ridwan Rais No.1, Gambir, Jakarta Pusat',
                'latitude' => -6.175392,
                'longitude' => 106.827153,
                'jam_operasional' => '24 Jam',
                'status' => 'aktif',
                'chargers' => [
                    ['type' => 'CCS2 (Fast Charging)', 'power_kw' => 50, 'status' => 'available'],
                    ['type' => 'Type 2 (AC)', 'power_kw' => 22, 'status' => 'available'],
                ]
            ],
            [
                'nama_lokasi' => 'SPKLU Gedung Sate Bandung',
                'alamat' => 'Jl. Diponegoro No.22, Citarum, Bandung',
                'latitude' => -6.902481,
                'longitude' => 107.618810,
                'jam_operasional' => '24 Jam',
                'status' => 'aktif',
                'chargers' => [
                    ['type' => 'CCS2 (Fast Charging)', 'power_kw' => 60, 'status' => 'available'],
                ]
            ],
            [
                'nama_lokasi' => 'SPKLU PLN UP3 Surabaya Selatan',
                'alamat' => 'Jl. Ngagel Timur No.14, Surabaya',
                'latitude' => -7.291702,
                'longitude' => 112.753331,
                'jam_operasional' => '24 Jam',
                'status' => 'aktif',
                'chargers' => [
                    ['type' => 'Chademo', 'power_kw' => 50, 'status' => 'available'],
                ]
            ],
        ];

        foreach ($locations as $data) {
            $chargers = $data['chargers'];
            unset($data['chargers']);

            $location = Location::create($data);

            foreach ($chargers as $charger) {
                $charger['location_id'] = $location->id_location ?? $location->id;
                Charger::create($charger);
            }
        }
    }
}