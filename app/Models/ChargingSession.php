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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function charger()
    {
        // Mengecek foreign key 'id_charger', atau fallback ke 'charger_id' jika null
        $foreignKey = $this->id_charger ? 'id_charger' : 'charger_id';
        
        return $this->belongsTo(Charger::class, $foreignKey, 'id_charger');
    }

    public function vehicle()
    {
        $foreignKey = $this->id_vehicle ? 'id_vehicle' : 'vehicle_id';

        return $this->belongsTo(Vehicle::class, $foreignKey, 'id_vehicle');
    }

    public function reservation()
    {
        $foreignKey = $this->id_reservation ? 'id_reservation' : 'reservation_id';

        return $this->belongsTo(Reservation::class, $foreignKey, 'id_reservation');
    }
}