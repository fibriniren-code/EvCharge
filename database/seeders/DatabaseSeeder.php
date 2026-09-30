<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Charger;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $loc1 = Location::create([
            'nama_lokasi' => 'SPKLU Malioboro',
            'alamat' => 'Jl. Malioboro, Yogyakarta',
            'latitude' => -7.7928,
            'longitude' => 110.3658,
            'jam_operasional' => '24 Jam',
            'status' => 'aktif',
        ]);

        $loc2 = Location::create([
            'nama_lokasi' => 'SPKLU Voltron Yogyakarta',
            'alamat' => 'Jl. C. Simanjuntak No.1, Yogyakarta',
            'latitude' => -7.781373,
            'longitude' => 110.379174,
            'jam_operasional' => '24 Jam',
            'status' => 'aktif',
        ]);

        Charger::create([
            'location_id' => $loc1->id_location,
            'type' => 'CCS2 (Fast Charging)',
            'power_kw' => 50,
            'status' => 'available',
        ]);

        Charger::create([
            'location_id' => $loc2->id_location,
            'type' => 'Type 2 (AC)',
            'power_kw' => 22,
            'status' => 'available',
        ]);
    }
}