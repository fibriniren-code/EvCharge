<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id',
        'make',
        'model',
        'year',
        'battery_capacity',
        'license_plate',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
