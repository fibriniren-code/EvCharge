<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Charger;
use App\Models\Location;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationController extends Controller
{
    // Halaman Form Reservasi (Membawa Data SPKLU & Charger)
    public function create($location_id, $charger_id)
    {
        $location = Location::findOrFail($location_id);
        $charger = Charger::where('location_id', $location_id)->findOrFail($charger_id);
        $vehicles = auth()->user()->vehicles; // Ambil kendaraan milik user

        return view('driver.reservations.create', compact('location', 'charger', 'vehicles'));
    }

    // Proses Simpan Reservasi
    public function store(Request $request)
    {
        $request->validate([
            'charger_id' => 'required|exists:chargers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_time' => 'required|date|after:now',
            'duration_hours' => 'required|integer|min:1|max:8',
        ]);

        $startTime = Carbon::parse($request->start_time);
        $endTime = (clone $startTime)->addHours((int)$request->duration_hours);

        // Cek apakah slot charger sudah dibooking pada jam tersebut
        $isBooked = Reservation::where('charger_id', $request->charger_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                      });
            })->exists();

        if ($isBooked) {
            return back()->withErrors(['start_time' => 'Slot waktu pada charger ini sudah dipesan orang lain. Silakan pilih waktu/charger lain.']);
        }

        // Simpan Reservasi
        $reservation = Reservation::create([
            'user_id' => auth()->id(),
            'charger_id' => $request->charger_id,
            'vehicle_id' => $request->vehicle_id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'confirmed',
        ]);

        return redirect()->route('reservations.index')->with('success', 'Reservasi slot charger berhasil dibuat!');
    }

    // Daftar Reservasi Saya
    public function index()
    {
        $reservations = Reservation::with(['charger.location', 'vehicle'])
            ->where('user_id', auth()->id())
            ->orderBy('start_time', 'desc')
            ->get();

        return view('driver.reservations.index', compact('reservations'));
    }
}