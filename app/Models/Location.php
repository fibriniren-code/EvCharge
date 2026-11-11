<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'locations';
    protected $primaryKey = 'id_location';

    protected $fillable = [
        'nama_lokasi',
        'alamat',
        'detail_area',
        'latitude',
        'longitude',
        'jam_operasional',
        'fasilitas',
        'status',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function chargers()
    {
        return $this->hasMany(Charger::class, 'location_id', 'id_location');
    }

    public function tariffs()
    {
        return $this->hasMany(Tariff::class, 'id_location', 'id_location');
    }

    // Tarif yang sedang berlaku (periode_berlaku terbaru yang sudah lewat)
    public function currentTariff(): ?Tariff
    {
        return $this->tariffs()
            ->where('periode_berlaku', '<=', now())
            ->latest('periode_berlaku')
            ->first();
    }
}
