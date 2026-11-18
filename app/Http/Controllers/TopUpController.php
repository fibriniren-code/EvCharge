<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TopUpController extends Controller
{
    public const METHODS = ['QRIS', 'E-Wallet', 'Virtual Account', 'Kartu Kredit'];

    public function index(Request $request)
    {
        $user = $request->user();

        return view('driver.topup', [
            'payments' => Payment::where('user_id', $user->id)->whereNull('id_session')->latest('id_payment')->take(6)->get(),
            'outstanding' => $user->outstandingAmount(),
            'methods' => self::METHODS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000', 'max:10000000'],
            'metode' => ['required', 'in:' . implode(',', self::METHODS)],
        ]);

        $settled = 0.0;

        DB::transaction(function () use ($request, $data, &$settled) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $user->increment('balance', (float) $data['amount']);

            Payment::create([
                'user_id' => $user->id,
                'metode' => $data['metode'],
                'jumlah' => $data['amount'],
                'status' => 'success',
                'waktu_pembayaran' => now(),
                'referensi_gateway' => 'TOPUP-' . Str::upper(Str::random(8)),
            ]);

            // Tagihan tertunggak (kekurangan saldo sesi sebelumnya) dilunasi lebih dulu.
            $settled = $user->settleOutstanding();
        });

        $message = 'Saldo berhasil ditambahkan: Rp ' . number_format((float) $data['amount'], 0, ',', '.') . '.';
        if ($settled > 0) {
            $message .= ' Tagihan tertunggak Rp ' . number_format($settled, 0, ',', '.') . ' otomatis dilunasi.';
        }

        return redirect()->route('topup.index')->with('success', $message);
    }
}