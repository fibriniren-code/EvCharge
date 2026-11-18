<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_reservation';

    protected $fillable = [
        'user_id',
        'charger_id',
        'vehicle_id',
        'waktu_mulai',
        'waktu_selesai',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function charger()
    {
        return $this->belongsTo(Charger::class, 'charger_id', 'id_charger');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id_vehicle');
    }

    // Sesi charging terbaru milik reservasi ini
    public function chargingSession()
    {
        return $this->hasOne(ChargingSession::class, 'id_reservation', 'id_reservation')
            ->latestOfMany('id_session');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'active'])
                     ->where('expires_at', '>', now());
    }

    // Reservasi yang sedang menahan port: pending/confirmed dan belum kedaluwarsa
    public function scopeHolding($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed'])
                     ->where('expires_at', '>', now());
    }

    // Tandai reservasi yang lewat batas waktu sebagai expired dan bebaskan port-nya.
    // Mengembalikan jumlah reservasi yang diperbarui.
    public static function expireStale(): int
    {
        $stale = static::with('charger')
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($stale as $reservation) {
            $reservation->update(['status' => 'expired']);
            $reservation->charger?->syncReservedState();
        }

        return $stale->count();
    }
}