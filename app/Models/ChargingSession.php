<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'charger_id',
        'start_time',
        'end_time',
        'total_kwh',
        'total_cost',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function charger()
    {
        return $this->belongsTo(Charger::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
