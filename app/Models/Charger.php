<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charger extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_charger';

    protected $fillable = [
        'location_id',
        'charger_type',
        'power_kw',
        'price_per_kwh',
        'status',
    ];

    protected $casts = [
        'power_kw' => 'decimal:2',
        'price_per_kwh' => 'decimal:2',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'id_location');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'charger_id', 'id_charger');
    }

    // PERBAIKAN DI SINI: tambahkan 'id_charger' sebagai foreign key
    public function chargingSessions()
    {
        return $this->hasMany(ChargingSession::class, 'id_charger', 'id_charger');
    }

    public function isAvailable(): bool
    {
        if ($this->status !== 'available') {
            return false;
        }

        $hasActiveSession = $this->chargingSessions()
            ->where('status', 'active')
            ->exists();

        $hasActiveReservation = $this->reservations()
            ->whereIn('status', ['pending', 'active'])
            ->where('expires_at', '>', now())
            ->exists();

        return !$hasActiveSession && !$hasActiveReservation;
    }
}