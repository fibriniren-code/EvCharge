<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $fillable = [
        'charger_id',
        'price_per_kwh',
        'currency',
    ];

    public function charger()
    {
        return $this->belongsTo(Charger::class);
    }
}
