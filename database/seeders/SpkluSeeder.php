<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpkluSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('locations')->insert([
            [
                'name' => 'SPKLU PLN ULP Malioboro',
                'address' => 'Jl. Malioboro No.60, Sosromenduran, Gedong Tengen, Yogyakarta',
                'charger_type' => 'DC_CCS2',
                'status' => 'available',
                'tariff' => 'Rp 2.475 / kWh',
                'description' => 'Stasiun pengisian fast charging publik yang berlokasi strategis di kawasan pusat kota Yogyakarta.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SPKLU Rest Area KM 379A',
                'address' => 'Jl. Tol Batang-Semarang KM 379A',
                'charger_type' => 'AC',
                'status' => 'available',
                'tariff' => 'Rp 1.650 / kWh',
                'description' => 'Fasilitas charging station untuk pengendara mobil listrik yang melintas di jalur tol trans-jawa.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SPKLU PLN UP3 Yogyakarta',
                'address' => 'Jl. Laksda Adisucipto KM 7, Depok, Sleman',
                'charger_type' => 'DC_CHAdeMO',
                'status' => 'busy',
                'tariff' => 'Rp 2.475 / kWh',
                'description' => 'Pusat layanan pengisian daya kendaraan listrik dengan berbagai pilihan port colokan standar.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}