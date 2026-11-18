<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charger extends Model
{
    use HasFactory;

    protected $table = 'chargers';

    protected $primaryKey = 'id_charger';

    protected $fillable = [
        'location_id',
        'charger_type',
        'power_kw',
        'status',
    ];

    protected $casts = [
        'power_kw' => 'decimal:2',
    ];

    // Relasi ke lokasi SPKLU
    public function location()
    {
        return $this->belongsTo(
            Location::class,
            'location_id',
            'id_location'
        );
    }

    // Relasi ke reservasi
    public function reservations()
    {
        return $this->hasMany(
            Reservation::class,
            'charger_id',
            'id_charger'
        );
    }

    // Relasi ke sesi charging
    public function chargingSessions()
    {
        return $this->hasMany(
            ChargingSession::class,
            'id_charger',
            'id_charger'
        );
    }

    // Sesi charging yang sedang berjalan di port ini
    public function runningSession()
    {
        return $this->hasOne(ChargingSession::class, 'id_charger', 'id_charger')
            ->where('status', 'running')
            ->latest('id_session');
    }

    // Reservasi yang saat ini menahan port (pending/confirmed dan belum kedaluwarsa)
    public function activeHolds()
    {
        return $this->reservations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('expires_at', '>', now());
    }

    // Memeriksa ketersediaan charger
    public function isAvailable(): bool
    {
        if ($this->status !== 'available') {
            return false;
        }

        $hasActiveSession = $this->chargingSessions()
            ->where('status', 'running')
            ->exists();

        return ! $hasActiveSession && ! $this->activeHolds()->exists();
    }

    // Batalkan semua reservasi aktif (pending/confirmed). Mengembalikan jumlah yang dibatalkan.
    public function cancelActiveReservations(): int
    {
        $count = $this->reservations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->update(['status' => 'cancelled']);

        if ($count > 0 && $this->status === 'reserved') {
            $this->update(['status' => 'available']);
        }

        return $count;
    }

    // Kembalikan status ke "available" bila port berstatus "reserved"
    // tetapi sudah tidak ada reservasi yang menahannya.
    public function syncReservedState(): void
    {
        if ($this->status === 'reserved' && ! $this->activeHolds()->exists()) {
            $this->update(['status' => 'available']);
        }
    }

    // Ubah status port secara manual (operator/admin).
    // Mengembalikan [berhasil (bool), pesan (string)].
    public function setOperationalStatus(string $target): array
    {
        if (! in_array($target, ['available', 'unavailable', 'faulted'], true)) {
            return [false, 'Status tidak valid.'];
        }

        if ($this->status === 'occupied' || $this->chargingSessions()->where('status', 'running')->exists()) {
            return [false, 'Port sedang dipakai pengemudi. Hentikan sesi charging terlebih dahulu.'];
        }

        if ($target === 'available') {
            $this->update(['status' => $this->activeHolds()->exists() ? 'reserved' : 'available']);

            return [true, "Charger #{$this->id_charger} kini tersedia."];
        }

        $cancelled = $this->cancelActiveReservations();
        $this->update(['status' => $target]);

        $label = $target === 'faulted' ? 'ditandai rusak' : 'dinonaktifkan';
        $extra = $cancelled > 0 ? " {$cancelled} reservasi aktif dibatalkan." : '';

        return [true, "Charger #{$this->id_charger} {$label}.{$extra}"];
    }
}