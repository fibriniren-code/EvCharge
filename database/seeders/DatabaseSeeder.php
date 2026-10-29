<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Charger;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- YOGYAKARTA ---
        $loc1 = Location::create([
            'nama_lokasi'     => 'SPKLU Malioboro',
            'alamat'          => 'Jl. Malioboro No. 60, Yogyakarta',
            'detail_area'     => 'Area Parkir Utama',
            'latitude'        => -7.7928000,
            'longitude'       => 110.3658000,
            'jam_operasional' => '24 Jam',
            'fasilitas'       => json_encode(['Toilet', 'Wifi', 'Cafe']),
            'status'          => 'aktif',
        ]);

        $loc2 = Location::create([
            'nama_lokasi'     => 'SPKLU Voltron Yogyakarta',
            'alamat'          => 'Jl. C. Simanjuntak No.1, Yogyakarta',
            'detail_area'     => 'Halaman Depan',
            'latitude'        => -7.7813730,
            'longitude'       => 110.3791740,
            'jam_operasional' => '24 Jam',
            'fasilitas'       => json_encode(['Minimarket', 'Musholla']),
            'status'          => 'aktif',
        ]);

        // --- SURABAYA ---
        $loc3 = Location::create([
            'nama_lokasi'     => 'SPKLU PLN UP3 Surabaya Selatan',
            'alamat'          => 'Jl. Ngagel Timur No. 14, Surabaya',
            'detail_area'     => 'Area Parkir Depan',
            'latitude'        => -7.2891230,
            'longitude'       => 112.7534560,
            'jam_operasional' => '24 Jam',
            'fasilitas'       => json_encode(['Toilet', 'Ruang Tunggu', 'Wifi']),
            'status'          => 'aktif',
        ]);

        $loc4 = Location::create([
            'nama_lokasi'     => 'SPKLU Grand City Mall Surabaya',
            'alamat'          => 'Jl. Walikota Mustajab No. 1, Surabaya',
            'detail_area'     => 'Gedung Parkir B1',
            'latitude'        => -7.2625000,
            'longitude'       => 112.7483000,
            'jam_operasional' => '10:00 - 22:00',
            'fasilitas'       => json_encode(['Mall', 'Restoran', 'Toilet']),
            'status'          => 'aktif',
        ]);

        // --- JAKARTA ---
        $loc5 = Location::create([
            'nama_lokasi'     => 'SPKLU PLN UID Jaya Gambir',
            'alamat'          => 'Jl. M.I. Ridwan Rais No. 1, Jakarta Pusat',
            'detail_area'     => 'Halaman Kantor PLN',
            'latitude'        => -6.1785000,
            'longitude'       => 106.8321000,
            'jam_operasional' => '24 Jam',
            'fasilitas'       => json_encode(['Musholla', 'Toilet', 'Kantin']),
            'status'          => 'aktif',
        ]);

        // --- CHARGERS ---
        if (class_exists(Charger::class)) {
            Charger::create(['location_id' => $loc1->id_location, 'type' => 'CCS2 (Fast Charging)', 'power_kw' => 50, 'status' => 'available']);
            Charger::create(['location_id' => $loc2->id_location, 'type' => 'Type 2 (AC)', 'power_kw' => 22, 'status' => 'available']);
            Charger::create(['location_id' => $loc3->id_location, 'type' => 'CCS2 Ultra Fast Charging', 'power_kw' => 100, 'status' => 'available']);
            Charger::create(['location_id' => $loc4->id_location, 'type' => 'Type 2 (AC)', 'power_kw' => 22, 'status' => 'available']);
            Charger::create(['location_id' => $loc5->id_location, 'type' => 'CCS2 + CHAdeMO', 'power_kw' => 60, 'status' => 'available']);
        }
    }
}