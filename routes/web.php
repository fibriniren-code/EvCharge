<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\Driver\ChargingSessionController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// --- PUBLIC ROUTES ---
Route::get('/', function () {
    return view('welcome');
});

// --- AUTHENTICATED ROUTES ---
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard Utama
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // --- PROFILE ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- KELOLA KENDARAAN LISTRIK (VEHICLES) ---
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('cars.index');
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('cars.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

    // --- SPKLU & LOKASI ---
    Route::get('/spklu', [LocationController::class, 'index'])->name('spklu.index');
    Route::get('/spklu/{id}', [LocationController::class, 'show'])->name('spklu.show');
    Route::get('/api/locations/nearby', [LocationController::class, 'getNearbyLocations'])->name('api.locations.nearby');
    Route::get('/spklu/{location_id}/chargers/status', [ReservationController::class, 'getSlotStatuses'])->name('chargers.status');

    // --- RESERVASI SLOT CHARGER ---
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/location/{location}/charger/{charger}/reserve', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{id}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::get('/reservations/{id}/timer', [ReservationController::class, 'getTimer'])->name('reservations.timer');
    Route::post('/reservations/{id}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::match(['post', 'patch'], '/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    
    // Stop Charging Reservasi (Mendukung nama route 'reservations.stop' dan 'charging.stop')
    Route::post('/reservations/{id}/stop', [ReservationController::class, 'stopCharging'])->name('reservations.stop');

    // --- SESI CHARGING REAL-TIME ---
    Route::get('/scan', function () {
        return view('driver.charging.scan');
    })->name('driver.scan');

    Route::post('/charging/process-scan', [ChargingSessionController::class, 'processScan'])->name('driver.charging.processScan');
    Route::post('/charging/start', [ChargingSessionController::class, 'startSession'])->name('driver.charging.start');
    
    // Monitoring & Telemetry Real-Time
    Route::get('/charging/session/{id}', [ChargingSessionController::class, 'monitorSession'])->name('driver.charging.monitor');
    Route::get('/charging/session/{id}/telemetry', [ChargingSessionController::class, 'getTelemetry'])->name('driver.charging.telemetry');

    // Stop Session & Struk Biaya
    Route::post('/charging/session/{id}/stop', [ChargingSessionController::class, 'stopSession'])->name('driver.charging.stop');
    Route::get('/charging/receipt/{id_session}', [ChargingSessionController::class, 'showReceipt'])->name('driver.charging.receipt');

    // --- ULASAN & FEEDBACK ---
    Route::post('/spklu/{location_id}/review', [ReviewController::class, 'store'])->name('reviews.store');

    // --- FITUR DRIVER / RIWAYAT / TOP UP ---
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

    // --- API ENDPOINT ---
    Route::get('/api/locations', [LocationController::class, 'index'])->name('api.locations.index');

});

require __DIR__.'/auth.php';