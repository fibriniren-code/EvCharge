<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tambahkan baris ini untuk menjalankan cleanup otomatis setiap menit
Schedule::command('reservations:cleanup-expired')->everyMinute();