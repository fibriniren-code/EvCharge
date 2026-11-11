<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $primaryKey = 'id_payment';

    protected $fillable = [
        'user_id',
        'id_session',
        'metode',
        'jumlah',
        'status',
        'waktu_pembayaran',
        'referensi_gateway',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'waktu_pembayaran' => 'datetime',
    ];

    public function chargingSession()
    {
        return $this->belongsTo(ChargingSession::class, 'id_session', 'id_session');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
