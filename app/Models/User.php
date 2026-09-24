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
            'password' => 'hashed',
            'balance' => 'decimal:2', // Cast balance ke tipe desimal
        ];
    }

    /* Relasi: Satu user dapat memiliki banyak kendaraan EV (FR-01).*/
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /*Relasi: Satu user dapat memiliki banyak riwayat sesi charging (FR-05).*/
    public function chargingSessions(): HasMany
    {
        return $this->hasMany(ChargingSession::class);
    }

    /* Relasi: Satu user dapat memberikan banyak review (FR-11).*/
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}