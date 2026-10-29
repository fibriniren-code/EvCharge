<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Location::create([
            'name' => 'SPKLU Malbor',
            'address' => 'Jl. Marlborough No. 12, Kebayoran Baru',
            'area_detail' => 'Area Parkir VIP / Samping Coffee Shop',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);
    }
}