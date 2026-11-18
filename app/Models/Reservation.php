<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function chargingSession()
    {
        return $this->hasOne(ChargingSession::class, 'id_reservation', 'id_reservation')
            ->latestOfMany('id_session');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'active'])
            ->where('expires_at', '>', now());
    }

    public function scopeHolding($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed'])
            ->where('expires_at', '>', now());
    }

    public static function expireStale(): int
    {
        $processed = 0;

        // Proses no-show untuk reservasi yang sudah dikonfirmasi.
        $noShows = static::where('status', 'confirmed')
            ->where('waktu_mulai', '<=', now()->subMinutes(15))
            ->whereDoesntHave('chargingSession')
            ->pluck('id_reservation');

        foreach ($noShows as $id) {
            $wasProcessed = DB::transaction(function () use ($id) {
                $reservation = static::whereKey($id)
                    ->lockForUpdate()
                    ->first();

                if (
                    ! $reservation ||
                    $reservation->status !== 'confirmed' ||
                    $reservation->waktu_mulai->gt(now()->subMinutes(15)) ||
                    $reservation->chargingSession()->exists()
                ) {
                    return false;
                }

                $user = User::whereKey($reservation->user_id)
                    ->lockForUpdate()
                    ->first();

                $reservation->update(['status' => 'expired']);

                if ($user) {
                    $user->no_show_count = (int) $user->no_show_count + 1;

                    if ($user->no_show_count >= 3) {
                        $user->reservation_blocked_until = now()->addHours(24);
                    }

                    $user->save();
                }

                $reservation->charger?->syncReservedState();

                return true;
            });

            if ($wasProcessed) {
                $processed++;
            }
        }

        // Pending yang melewati batas waktu konfirmasi tetap expired,
        // tetapi tidak dihitung sebagai no-show.
        $stale = static::with('charger')
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($stale as $reservation) {
            $reservation->update(['status' => 'expired']);
            $reservation->charger?->syncReservedState();
            $processed++;
        }

        return $processed;
    }
}
