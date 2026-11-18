<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Location extends Model
{
    use HasFactory;

    protected $table = 'locations';
    protected $primaryKey = 'id_location';

    protected $fillable = [
        'nama_lokasi',
        'alamat',
        'detail_area',
        'latitude',
        'longitude',
        'jam_operasional',
        'fasilitas',
        'status',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function chargers()
    {
        return $this->hasMany(Charger::class, 'location_id', 'id_location');
    }

    public function tariffs()
    {
        return $this->hasMany(Tariff::class, 'id_location', 'id_location');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'id_location', 'id_location');
    }

    // Tarif yang sedang berlaku (periode_berlaku terbaru yang sudah lewat)
    public function currentTariff(): ?Tariff
    {
        return $this->tariffs()
            ->where('periode_berlaku', '<=', now())
            ->latest('periode_berlaku')
            ->first();
    }

    public function isOpen(): bool
    {
        return $this->status === 'aktif';
    }

    // Kolom fasilitas disimpan sebagai string JSON, jadi dinormalkan menjadi array
    public function getFasilitasListAttribute(): array
    {
        $value = $this->attributes['fasilitas'] ?? null;

        for ($i = 0; $i < 2 && is_string($value); $i++) {
            $decoded = json_decode($value, true);
            if ($decoded === null) {
                break;
            }
            $value = $decoded;
        }

        return is_array($value) ? array_values(array_filter($value, 'is_string')) : [];
    }

    // Tutup stasiun, lalu batalkan reservasi aktif di semua port.
    // Mengembalikan jumlah reservasi yang dibatalkan.
    public function close(): int
    {
        $cancelled = 0;

        DB::transaction(function () use (&$cancelled) {
            $this->update(['status' => 'tutup']);

            foreach ($this->chargers as $charger) {
                $cancelled += $charger->cancelActiveReservations();
            }
        });

        return $cancelled;
    }

    public function open(): void
    {
        $this->update(['status' => 'aktif']);
    }
}