<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;

class CleanupExpiredReservations extends Command
{
    protected $signature = 'reservations:cleanup';
    protected $description = 'Menandai reservasi yang melewati batas waktu sebagai kedaluwarsa dan membebaskan port-nya';

    public function handle(): int
    {
        $count = Reservation::expireStale();

        $this->info($count > 0
            ? "Berhasil memperbarui {$count} reservasi yang kedaluwarsa."
            : 'Tidak ada reservasi kedaluwarsa yang perlu dibersihkan.');

        return self::SUCCESS;
    }
}