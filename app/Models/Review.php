<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $primaryKey = 'id_review';

    protected $fillable = [
        'user_id',
        'id_location',
        'id_session',
        'rating_alat',
        'ulasan_alat',
        'rating_tempat',
        'ulasan_tempat',
        'foto_lokasi',
        'balasan_operator',
    ];

    protected $casts = [
        'rating_alat' => 'integer',
        'rating_tempat' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo(
            Location::class,
            'id_location',
            'id_location'
        );
    }

    public function chargingSession()
    {
        return $this->belongsTo(
            ChargingSession::class,
            'id_session',
            'id_session'
        );
    }
}
