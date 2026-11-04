<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargingSession extends Model
{
    use HasFactory;

    protected $table = 'charging_sessions';

    protected $primaryKey = 'id_session';

    protected $guarded = [];

    protected $casts = [
        'start_time'      => 'datetime',
        'waktu_mulai'     => 'datetime',
        'end_time'        => 'datetime',
        'waktu_selesai'   => 'datetime',
        'energy_consumed' => 'decimal:2',
        'energy_kwh'      => 'decimal:2',
        'total_cost'      => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function charger()
    {
        // Tentukan foreign key langsung sesuai kolom tabel DB ('id_charger')
        return $this->belongsTo(Charger::class, 'id_charger', 'id_charger');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'id_vehicle', 'id_vehicle');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'id_reservation', 'id_reservation');
    }
}