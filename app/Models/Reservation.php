<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'charger_id',
        'vehicle_id',
        'start_time',
        'end_time',
        'status',
        'total_cost',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function charger()
    {
        return $this->belongsTo(Charger::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}