<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    // Custom primary key sesuai database
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
}