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

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'active'])
                     ->where('expires_at', '>', now());
    }
}