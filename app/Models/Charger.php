<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charger extends Model
{
    use HasFactory;

    protected $table = 'chargers';

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

    // Relasi ke lokasi SPKLU
    public function location()
    {
        return $this->belongsTo(
            Location::class,
            'location_id',
            'id_location'
        );
    }

    // Relasi ke reservasi
    public function reservations()
    {
        return $this->hasMany(
            Reservation::class,
            'charger_id',
            'id_charger'
        );
    }

    // Relasi ke sesi charging
    public function chargingSessions()
    {
        return $this->hasMany(
            ChargingSession::class,
            'id_charger',
            'id_charger'
        );
    }

    // Memeriksa ketersediaan charger
    public function isAvailable(): bool
    {
        if ($this->status !== 'available') {
            return false;
        }

        $hasActiveSession = $this->chargingSessions()
            ->where('status', 'running')
            ->exists();

        $hasActiveReservation = $this->reservations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('expires_at', '>', now())
            ->exists();

        return !$hasActiveSession && !$hasActiveReservation;
    }
}
