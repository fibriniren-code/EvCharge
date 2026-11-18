<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_vehicle';

    protected $fillable = [
        'user_id',
        'merek',
        'model',
        'nomor_polisi',
        'tipe_konektor',
        'kapasitas_baterai_kwh',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // "CCS2 (Fast Charging)" menjadi "ccs2"
    public static function connectorKey(string $connector): string
    {
        return strtolower(trim(preg_split('/\s*\(/', $connector)[0]));
    }

    // Cek apakah konektor kendaraan cocok dengan jenis charger
    public function isCompatibleWith(Charger $charger): bool
    {
        $key = self::connectorKey((string) $this->tipe_konektor);

        return $key !== '' && str_contains(strtolower((string) $charger->charger_type), $key);
    }
}