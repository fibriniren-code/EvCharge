<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\StationController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Driver\ChargingSessionController;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboardController;
use App\Http\Controllers\Operator\ReviewController as OperatorReviewController;
use App\Http\Controllers\Operator\SessionController as OperatorSessionController;
use App\Http\Controllers\Operator\StationController as OperatorStationController;
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
})->name('home');


// AUTHENTICATED ROUTES
Route::middleware(['auth', 'verified'])->group(function () {

    // DASHBOARD — mengarahkan setiap peran ke dashboard miliknya
    Route::get('/dashboard', DashboardController::class)->name('dashboard');


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
        Route::get('/scan', [ChargingSessionController::class, 'scan'])
            ->name('driver.scan');

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


        // TOP UP
        Route::get('/balance/topup', [TopUpController::class, 'index'])->name('topup.index');
        Route::post('/balance/topup', [TopUpController::class, 'store'])->name('topup.store');
    });


    // =========================================================
    // OPERATOR (FR-09 s/d FR-12)
    // =========================================================
    Route::middleware('role:operator')->prefix('operator')->name('operator.')->group(function () {

        // Monitoring port real-time (FR-09)
        Route::get('/dashboard', [OperatorDashboardController::class, 'index'])
            ->name('dashboard');

        // Kendali stasiun & port (FR-10)
        Route::get('/stations', [OperatorStationController::class, 'index'])
            ->name('stations.index');

        Route::patch('/stations/{station}/toggle', [OperatorStationController::class, 'toggle'])
            ->name('stations.toggle');

        Route::patch('/chargers/{charger}/status', [OperatorStationController::class, 'chargerStatus'])
            ->name('chargers.status');

        // Sesi charging & henti darurat (FR-12)
        Route::get('/sessions', [OperatorSessionController::class, 'index'])
            ->name('sessions.index');

        Route::post('/sessions/{session}/stop', [OperatorSessionController::class, 'stop'])
            ->name('sessions.stop');

        // Ulasan & keluhan pengemudi (FR-11)
        Route::get('/reviews', [OperatorReviewController::class, 'index'])
            ->name('reviews.index');

        Route::post('/reviews/{review}/reply', [OperatorReviewController::class, 'reply'])
            ->name('reviews.reply');
    });


    // =========================================================
    // ADMIN
    // =========================================================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // Stasiun (tanpa "show", halaman detail tidak dipakai)
        Route::resource('stations', StationController::class)->except(['show']);

        Route::patch('/stations/{station}/toggle-status', [StationController::class, 'toggleStatus'])
            ->name('stations.toggle-status');

        // Charger milik stasiun
        Route::post('/stations/{station}/chargers', [StationController::class, 'storeCharger'])
            ->name('stations.chargers.store');

        Route::delete('/chargers/{charger}', [StationController::class, 'destroyCharger'])
            ->name('chargers.destroy');

        // Tarif per stasiun (parameter kini {location}, bukan {tariff})
        Route::get('/tariffs', [TariffController::class, 'index'])
            ->name('tariffs.index');

        Route::post('/tariffs/{location}/update', [TariffController::class, 'update'])
            ->name('tariffs.update');

        // Kelola pengguna (buat akun operator/admin, aktif/nonaktif)
        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

        Route::post('/users', [UserController::class, 'store'])
            ->name('users.store');

        Route::patch('/users/{user}/toggle', [UserController::class, 'toggle'])
            ->name('users.toggle');

        // Moderasi ulasan
        Route::get('/reviews', [AdminReviewController::class, 'index'])
            ->name('reviews.index');

        Route::post('/reviews/{review}/reply', [AdminReviewController::class, 'reply'])
            ->name('reviews.reply');

        Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])
            ->name('reviews.destroy');
    });
});


require __DIR__.'/auth.php';