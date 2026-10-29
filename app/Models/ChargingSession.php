<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargingSession extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_session';

    protected $fillable = [
        'user_id',
        'id_charger', // Sesuaikan jika di DB menggunakan id_charger
        'vehicle_id',
        'reservation_id',
        'start_time',
        'end_time',
        'energy_consumed',
        'total_cost',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'energy_consumed' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // PERBAIKAN DI SINI: gunakan 'id_charger'
    public function charger()
    {
        return $this->belongsTo(Charger::class, 'id_charger', 'id_charger');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id_vehicle');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'id_reservation');
    }
}