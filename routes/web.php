<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SPKLUController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;

// Halaman Utama / Landing Page
Route::get('/', function () {
    return view('welcome');
});

// Dashboard Utama
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Group Route khusus Pengguna Terautentikasi (Auth)
Route::middleware('auth')->group(function () {

    // --- PROFILE ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- KELOLA KENDARAAN LISTRIK (VEHICLES) ---
    // Diubah ke 'cars.index' sesuai dengan panggilan di dashboard.blade.php
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('cars.index');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

    // --- SPKLU & LOKASI ---
    Route::get('/spklu', [LocationController::class, 'index'])->name('spklu.index');
    Route::get('/spklu/{id}', [LocationController::class, 'show'])->name('spklu.show');

    // --- ENDPOINT API (UNTUK AJAX / FETCH PETA & LOKASI TERDEKAT) ---
    Route::get('/api/locations', [LocationController::class, 'index'])->name('api.locations.index');

    // --- DRIVER & FITUR UMUM ---
    Route::get('/charging-history', function () {
        return view('dashboard');
    })->name('transactions.index');

    Route::get('/balance/topup', function () {
        return view('dashboard');
    })->name('topup.index');

    Route::get('/support', function () {
        return view('dashboard');
    })->name('support.index');

    // --- OPERATOR SPKLU ---
    Route::get('/operator/dashboard', function () {
        return view('dashboard');
    })->name('operator.dashboard');

    Route::get('/operator/stations/create', function () {
        return view('dashboard');
    })->name('spklu.create');

    Route::middleware(['auth'])->group(function () {
    // Route Reservasi Slot Charger
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/location/{location}/charger/{charger}/reserve', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
});

});

require __DIR__.'/auth.php';