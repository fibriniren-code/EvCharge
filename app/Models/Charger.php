<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charger extends Model
{
    use HasFactory;

    protected $fillable = ['location_id', 'charger_code', 'type', 'status'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function chargingSessions()
    {
        return $this->hasMany(ChargingSession::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
