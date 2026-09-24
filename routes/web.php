<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Route bawaan Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route Tambahan SPKLU & Driver (Dummy view / silakan sesuaikan dengan Controller kamu kelak)
    Route::get('/spklu', function () { return view('dashboard'); })->name('spklu.index');
    Route::get('/vehicles', function () { return view('dashboard'); })->name('vehicles.index');
    Route::get('/charging-history', function () { return view('dashboard'); })->name('charging.history');
    Route::get('/balance/topup', function () { return view('dashboard'); })->name('balance.topup');
    Route::get('/support', function () { return view('dashboard'); })->name('support');

    // Route Operator (Bila ada)
    Route::get('/operator/dashboard', function () { return view('dashboard'); })->name('operator.dashboard');
    Route::get('/operator/stations/create', function () { return view('dashboard'); })->name('operator.stations.create');
});

require __DIR__.'/auth.php';