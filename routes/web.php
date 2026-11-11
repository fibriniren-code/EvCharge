<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\StationController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Driver\ChargingSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SPKLUController;
use App\Http\Controllers\TopUpController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;


// PUBLIC ROUTES
Route::get('/', function () {
    return view('welcome');
});


// AUTHENTICATED ROUTES
Route::middleware(['auth', 'verified'])->group(function () {

    // DASHBOARD — mengarahkan setiap peran ke dashboard miliknya
    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'admin'    => redirect()->route('admin.dashboard'),
            'operator' => redirect()->route('operator.dashboard'),
            default    => view('dashboard'),
        };
    })->name('dashboard');


    // PROFILE (semua peran)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // =========================================================
    // DRIVER
    // =========================================================
    Route::middleware('role:driver')->group(function () {

        // VEHICLES
        Route::get('/vehicles', [VehicleController::class, 'index'])->name('cars.index');
        Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('cars.create');
        Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');


        // SPKLU (FR-02)
        Route::get('/spklu', [SPKLUController::class, 'index'])->name('spklu.index');
        Route::get('/spklu/{id}', [SPKLUController::class, 'show'])->whereNumber('id')->name('spklu.show');
        Route::get('/spklu/{location_id}/chargers/status', [ReservationController::class, 'getSlotStatuses'])
            ->name('chargers.status');

        // API lokasi (JSON) untuk halaman peta
        Route::get('/api/locations', [SPKLUController::class, 'apiIndex'])->name('api.locations.index');
        Route::get('/api/locations/nearby', [SPKLUController::class, 'nearby'])->name('api.locations.nearby');


        // RESERVATIONS (FR-03)
        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('/location/{location}/charger/{charger}/reserve', [ReservationController::class, 'create'])->name('reservations.create');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::get('/reservations/{id}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::get('/reservations/{id}/timer', [ReservationController::class, 'getTimer'])->name('reservations.timer');
        Route::post('/reservations/{id}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
        Route::match(['post', 'patch'], '/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');


        // CHARGING (FR-04)
        Route::get('/scan', function () {
            return view('driver.charging.scan');
        })->name('driver.scan');

        Route::post('/charging/process-scan', [ChargingSessionController::class, 'processScan'])
            ->name('driver.charging.processScan');

        Route::post('/charging/start', [ChargingSessionController::class, 'startSession'])
            ->name('driver.charging.start');

        Route::get('/charging/session/{id}', [ChargingSessionController::class, 'monitorSession'])
            ->name('driver.charging.monitor');

        Route::get('/charging/session/{id}/telemetry', [ChargingSessionController::class, 'getTelemetry'])
            ->name('driver.charging.telemetry');

        Route::post('/charging/session/{id}/stop', [ChargingSessionController::class, 'stopSession'])
            ->name('driver.charging.stop');

        Route::post('/reservations/{id}/stop', [ChargingSessionController::class, 'stopSession'])
            ->name('charging.stop');

        Route::post('/charging/stop/{id}', [ChargingSessionController::class, 'stopSession'])
            ->name('reservations.stop');

        Route::get('/charging/receipt/{id_session}', [ChargingSessionController::class, 'showReceipt'])
            ->name('driver.charging.receipt');


        // TRANSACTION HISTORY (FR-06)
        Route::get('/charging-history', [ChargingSessionController::class, 'history'])
            ->name('transactions.index');


        // REVIEWS (FR-07)
        Route::get('/spklu/{location_id}/review', [ReviewController::class, 'create'])
            ->name('reviews.create');

        Route::post('/spklu/{location_id}/review', [ReviewController::class, 'store'])
            ->name('reviews.store');

        Route::get('/review-success', function () {
            return view('driver.reviews.success');
        })->name('reviews.success');


        // TOP UP & SUPPORT
        Route::get('/balance/topup', [TopUpController::class, 'index'])->name('topup.index');
        Route::post('/balance/topup', [TopUpController::class, 'store'])->name('topup.store');

        Route::get('/support', function () {
            return view('dashboard');
        })->name('support.index');
    });


    // =========================================================
    // OPERATOR (FR-09 s/d FR-12 — dikerjakan pada tahap berikutnya)
    // =========================================================
    Route::middleware('role:operator')->prefix('operator')->name('operator.')->group(function () {

        Route::get('/dashboard', function () {
            return view('operator.dashboard');
        })->name('dashboard');
    });


    // =========================================================
    // ADMIN
    // =========================================================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('stations', StationController::class);

        Route::patch('/stations/{station}/toggle-status', [StationController::class, 'toggleStatus'])
            ->name('stations.toggle-status');

        Route::get('/tariffs', [TariffController::class, 'index'])
            ->name('tariffs.index');

        Route::post('/tariffs/{tariff}/update', [TariffController::class, 'update'])
            ->name('tariffs.update');

        Route::get('/reviews', [AdminReviewController::class, 'index'])
            ->name('reviews.index');

        Route::post('/reviews/{review}/reply', [AdminReviewController::class, 'reply'])
            ->name('reviews.reply');
    });
});


require __DIR__.'/auth.php';
