<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $table = 'tariffs';

    protected $primaryKey = 'id_tariff';

    protected $fillable = [
        'id_location',
        'harga_per_kwh',
        'biaya_layanan',
        'biaya_minimum',
        'overstay_fee_per_menit',
        'periode_berlaku',
    ];

    protected $casts = [
        'harga_per_kwh' => 'decimal:2',
        'biaya_layanan' => 'decimal:2',
        'biaya_minimum' => 'decimal:2',
        'overstay_fee_per_menit' => 'decimal:2',
        'periode_berlaku' => 'datetime',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'id_location', 'id_location');
    }
}
