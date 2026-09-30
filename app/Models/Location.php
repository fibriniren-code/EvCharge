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
        'latitude',
        'longitude',
        'jam_operasional',
        'fasilitas',
        'status'
    ];

    protected $casts = [
        'fasilitas' => 'array',
    ];

    public function chargers()
    {
        return $this->hasMany(Charger::class, 'location_id', 'id_location');
    }
}