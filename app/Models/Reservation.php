<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'charger_id',
        'reservation_time',
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
}
