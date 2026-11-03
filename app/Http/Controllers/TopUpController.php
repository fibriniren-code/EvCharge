<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopUpController extends Controller
{
    public function index()
    {
        return view('driver.topup');
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->balance += $request->amount;
        $user->save();

        return redirect()->back()->with('success', 'Saldo berhasil ditambahkan!');
    }
}