<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'phone_number',
    'role',
    'status',
    'balance',
    'no_show_count',
    'reservation_blocked_until',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'no_show_count' => 'integer',
            'reservation_blocked_until' => 'datetime',
        ];
    }

    /**
     * Memeriksa role user.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Memeriksa apakah user sedang dibatasi untuk membuat reservasi.
     */
    public function isReservationBlocked(): bool
    {
        if (!$this->reservation_blocked_until) {
            return false;
        }

        if ($this->reservation_blocked_until->isFuture()) {
            return true;
        }

        // Jika masa pembatasan sudah berakhir,
        // reset waktu blokir dan jumlah no-show.
        $this->forceFill([
            'reservation_blocked_until' => null,
            'no_show_count' => 0,
        ])->save();

        return false;
    }

    /**
     * Menambahkan jumlah no-show user.
     * Jika mencapai 3 kali, user dibatasi selama 24 jam.
     */
    public function addNoShow(): void
    {
        $this->no_show_count = (int) $this->no_show_count + 1;

        if ($this->no_show_count >= 3) {
            $this->reservation_blocked_until = now()->addHours(24);
        }

        $this->save();
    }

    /**
     * Relasi user dengan kendaraan.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'user_id', 'id');
    }

    /**
     * Relasi user dengan reservasi.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'user_id', 'id');
    }

    /**
     * Relasi user dengan sesi charging.
     */
    public function chargingSessions(): HasMany
    {
        return $this->hasMany(ChargingSession::class, 'user_id', 'id');
    }

    /**
     * Relasi user dengan review.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id', 'id');
    }

    /**
     * Relasi pembayaran user melalui charging_sessions.
     */
    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Payment::class,
            ChargingSession::class,
            'user_id',
            'id_session',
            'id',
            'id_session'
        );
    }

    /**
     * Menghitung total tagihan pending.
     */
    public function outstandingAmount(): float
    {
        return (float) $this->payments()
            ->where('payments.status', 'pending')
            ->sum('payments.jumlah');
    }

    /**
     * Melunasi tagihan pending menggunakan saldo user.
     */
    public function settleOutstanding(): float
    {
        $settled = 0.0;

        $payments = $this->payments()
            ->where('payments.status', 'pending')
            ->orderBy('payments.id_payment')
            ->get();

        foreach ($payments as $payment) {
            $this->refresh();

            if ((float) $this->balance < (float) $payment->jumlah) {
                break;
            }

            $this->decrement('balance', (float) $payment->jumlah);

            $payment->update([
                'status' => 'success',
                'waktu_pembayaran' => now(),
            ]);

            $settled += (float) $payment->jumlah;
        }

        return $settled;
    }
}
