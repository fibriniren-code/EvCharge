<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Charger;
use App\Models\ChargingSession;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChargingSessionController extends Controller
{
    // Memulai Sesi via Scan QR / Aplikasi[cite: 3]
    public function startSession(Request $request)
    {
        $request->validate([
            'kode_perangkat' => 'required|exists:chargers,kode_perangkat',
        ]);

        $charger = Charger::where('kode_perangkat', $request->kode_perangkat)->first();

        if ($charger->status !== 'Available') {
            return response()->json(['message' => 'Port charger sedang tidak tersedia/digunakan.'], 400);
        }

        return DB::transaction(function () use ($charger) {
            // Update status charger menjadi Occupied[cite: 3]
            $charger->update(['status' => 'Occupied']);

            // Buat sesi charging baru[cite: 3]
            $session = ChargingSession::create([
                'user_id' => auth()->id(),
                'id_charger' => $charger->id_charger,
                'waktu_mulai' => Carbon::now(),
                'status' => 'running',
            ]);

            return response()->json([
                'message' => 'Sesi charging berhasil dimulai.',
                'data' => $session
            ], 201);
        });
    }

    // Menghentikan Sesi & Kalkulasi Otomatis (FR-05)[cite: 3]
    public function stopSession(Request $request, $id_session)
    {
        $session = ChargingSession::with('charger.location.tariffs')->findOrFail($id_session);

        if ($session->status !== 'running') {
            return response()->json(['message' => 'Sesi charging sudah selesai atau tidak aktif.'], 400);
        }

        return DB::transaction(function () use ($session, $request) {
            $waktuSelesai = Carbon::now();
            $energiKwh = $request->input('energi_kwh', 0); // Dioper dari telemetri perangkat[cite: 3]

            // Ambil tarif berlaku[cite: 3]
            $tariff = $session->charger->location->tariffs()->latest('periode_berlaku')->first();
            
            $hargaPerKwh = $tariff ? $tariff->harga_per_kwh : 0;
            $biayaLayanan = $tariff ? $tariff->biaya_layanan : 0;
            $biayaMinimum = $tariff ? $tariff->biaya_minimum : 0;

            // Perhitungan Biaya[cite: 3]
            $totalBiayaEnergi = $energiKwh * $hargaPerKwh;
            $totalBiaya = max($totalBiayaEnergi + $biayaLayanan, $biayaMinimum);

            // Update Sesi[cite: 3]
            $session->update([
                'waktu_selesai' => $waktuSelesai,
                'energi_kwh' => $energiKwh,
                'estimasi_biaya' => $totalBiaya,
                'status' => 'completed',
            ]);

            // Kembalikan status charger ke Available[cite: 3]
            $session->charger->update(['status' => 'Available']);

            // Buat record pembayaran awal[cite: 3]
            $payment = Payment::create([
                'id_session' => $session->id_session,
                'metode' => $request->metode_pembayaran ?? 'QRIS',
                'jumlah' => $totalBiaya,
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Sesi pengisian berhasil dihentikan.',
                'rincian_biaya' => [
                    'energi_terpakai_kwh' => $energiKwh,
                    'durasi_menit' => $session->waktu_mulai->diffInMinutes($waktuSelesai),
                    'total_tagihan' => $totalBiaya,
                ],
                'payment' => $payment
            ]);
        });
    }
}