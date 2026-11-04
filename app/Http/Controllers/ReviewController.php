<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Menampilkan form review
    public function create($location_id)
    {
        $location = Location::findOrFail($location_id);

        $reviewExists = Review::where('user_id', Auth::id())
            ->where('id_location', $location_id)
            ->exists();

        if ($reviewExists) {
            return redirect()
                ->route('spklu.index')
                ->with('info', 'Kamu sudah memberikan review untuk lokasi ini.');
        }

        return view('driver.reviews.create', compact('location'));
    }

    // Menyimpan review pengguna
    public function store(Request $request, $location_id)
    {
        $location = Location::findOrFail($location_id);

        $reviewExists = Review::where('user_id', Auth::id())
            ->where('id_location', $location_id)
            ->exists();

        if ($reviewExists) {
            return redirect()
            ->route('reviews.success')
            ->with('success', 'Review SPKLU berhasil disimpan!');
        }

        $validated = $request->validate([
            'rating_alat' => 'required|integer|min:1|max:5',
            'ulasan_alat' => 'nullable|string|max:1000',
            'rating_tempat' => 'required|integer|min:1|max:5',
            'ulasan_tempat' => 'nullable|string|max:1000',
            'foto_lokasi' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $foto = null;

        if ($request->hasFile('foto_lokasi')) {
            $foto = $request->file('foto_lokasi')
                ->store('reviews', 'public');
        }

        Review::create([
            'user_id' => Auth::id(),
            'id_location' => $location->id_location,
            'rating_alat' => $validated['rating_alat'],
            'ulasan_alat' => $validated['ulasan_alat'] ?? null,
            'rating_tempat' => $validated['rating_tempat'],
            'ulasan_tempat' => $validated['ulasan_tempat'] ?? null,
            'foto_lokasi' => $foto,
        ]);

        // Setelah review berhasil, kembali ke halaman awal SPKLU
        return redirect()
            ->route('spklu.index')
            ->with('success', 'Terima kasih! Review SPKLU berhasil dikirim.');
    }
}
