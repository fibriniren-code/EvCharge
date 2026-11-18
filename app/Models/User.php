<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone_number', 'role', 'status', 'balance'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'balance'           => 'decimal:2',
        ];
    }

    /* Memeriksa apakah user memiliki salah satu peran yang diberikan. */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /* Relasi: Satu user dapat memiliki banyak kendaraan EV (FR-01). */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'user_id', 'id');
    }

    /* Relasi: Satu user dapat memiliki banyak reservasi (FR-02). */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'user_id', 'id');
    }

    /* Relasi: Satu user dapat memiliki banyak riwayat sesi charging (FR-05). */
    public function chargingSessions(): HasMany
    {
        return $this->hasMany(ChargingSession::class, 'user_id', 'id');
    }

    /* Relasi: Satu user dapat memberikan banyak review (FR-11). */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id', 'id');
    }

    /* Relasi: riwayat pembayaran dan top-up milik user. */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id', 'id');
    }

    /* Total tagihan yang belum lunas (kekurangan saldo saat sesi selesai). */
    public function outstandingAmount(): float
    {
        return (float) $this->payments()->where('status', 'pending')->sum('jumlah');
    }

    /* Lunasi tagihan tertunggak dari saldo, urut dari yang terlama. Mengembalikan total yang dilunasi. */
    public function settleOutstanding(): float
    {
        $settled = 0.0;

        foreach ($this->payments()->where('status', 'pending')->orderBy('id_payment')->get() as $payment) {
            $this->refresh();

            if ((float) $this->balance < (float) $payment->jumlah) {
                break;
            }

            $this->decrement('balance', (float) $payment->jumlah);
            $payment->update(['status' => 'success', 'waktu_pembayaran' => now()]);
            $settled += (float) $payment->jumlah;
        }

        return $settled;
    }
}