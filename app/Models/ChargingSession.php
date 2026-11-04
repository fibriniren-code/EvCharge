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
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'energi_kwh' => 'decimal:2',
        'estimasi_biaya' => 'decimal:2',
    ];

    // Relasi ke pengguna
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }

    // Relasi ke charger
    public function charger()
    {
        return $this->belongsTo(
            Charger::class,
            'id_charger',
            'id_charger'
        );
    }

    // Relasi ke reservasi
    public function reservation()
    {
        return $this->belongsTo(
            Reservation::class,
            'id_reservation',
            'id_reservation'
        );
    }
}
