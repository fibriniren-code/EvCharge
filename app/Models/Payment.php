<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $primaryKey = 'id_payment';

    protected $fillable = [
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

    public function chargingSession(): BelongsTo
    {
        return $this->belongsTo(
            ChargingSession::class,
            'id_session',
            'id_session'
        );
    }

    /**
     * Mengambil user pemilik pembayaran melalui charging session.
     */
    public function getUserAttribute(): ?User
    {
        return $this->chargingSession?->user;
    }
}
