<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\Charger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CleanupExpiredReservations extends Command
{
    protected $signature = 'reservations:cleanup';
    protected $description = 'Membatalkan reservasi yang telah melewati batas waktu pembayaran/konfirmasi';

    public function handle()
    {
        $now = Carbon::now();

        $expiredReservations = Reservation::with(['charger.chargingSessions', 'charger.reservations'])
            ->where('status', 'pending')
            ->where('expires_at', '<=', $now)
            ->get();

        if ($expiredReservations->isEmpty()) {
            $this->info('Tidak ada reservasi expired yang perlu dibersihkan.');
            return 0;
        }

        $count = 0;

        foreach ($expiredReservations as $reservation) {
            DB::transaction(function () use ($reservation, &$count) {
                $reservation->update(['status' => 'expired']);
                $count++;

                $charger = $reservation->charger;

                if ($charger) {
                    $hasActiveSession = $charger->chargingSessions
                        ->where('status', 'active')
                        ->isNotEmpty();

                    $hasOtherActiveReservation = $charger->reservations
                        ->whereIn('status', ['pending', 'confirmed', 'active'])
                        ->where('id_reservation', '!=', $reservation->id_reservation)
                        ->where('expires_at', '>', now())
                        ->isNotEmpty();

                    if (!$hasActiveSession && !$hasOtherActiveReservation) {
                        $charger->update(['status' => 'available']);
                    }
                }
            });
        }

        $this->info("Berhasil memperbarui {$count} reservasi yang kadaluarsa.");
        return 0;
    }
}