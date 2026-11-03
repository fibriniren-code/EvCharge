<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargingSession extends Model
{
    use HasFactory;

    protected $table = 'charging_sessions';

    protected $primaryKey = 'id_session';

    /**
     * Menggunakan $guarded = [] agar semua kolom (waktu_mulai, start_time, id_vehicle, vehicle_id, dll)
     * diizinkan masuk ke query INSERT tanpa terbuang oleh fitur mass assignment protection.
     */
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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function charger()
    {
        return $this->belongsTo(Charger::class, 'id_charger', 'id_charger')
            ->withDefault(function ($charger, $session) {
                return Charger::find($session->charger_id ?? $session->id_charger);
            });
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id_vehicle')
            ->withDefault(function ($vehicle, $session) {
                return Vehicle::find($session->id_vehicle ?? $session->vehicle_id);
            });
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'id_reservation')
            ->withDefault(function ($res, $session) {
                return Reservation::find($session->id_reservation ?? $session->reservation_id);
            });
    }
}