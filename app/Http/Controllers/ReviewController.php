<?php

namespace App\Http\Controllers;

use App\Models\ChargingSession;
use App\Models\Location;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Cari sesi milik user yang boleh diulas di lokasi ini.
     * Jika $sessionId kosong, pakai sesi selesai terbaru yang belum diulas.
     */
    private function reviewableSession($location_id, $sessionId = null): ?ChargingSession
    {
        $query = ChargingSession::where('user_id', Auth::id())
            ->whereIn('status', ['completed', 'interrupted'])
            ->whereHas('charger.location', fn ($q) => $q->where('id_location', $location_id))
            ->whereNotIn('id_session', Review::where('user_id', Auth::id())
                ->whereNotNull('id_session')
                ->select('id_session'));

        if ($sessionId) {
            $query->where('id_session', $sessionId);
        }

        return $query->latest('id_session')->first();
    }

    // Menampilkan form review
    public function create(Request $request, $location_id)
    {
        $location = Location::findOrFail($location_id);

        $session = $this->reviewableSession($location_id, $request->query('session'));

        if (! $session) {
            return redirect()
                ->route('spklu.show', $location_id)
                ->with('info', 'Tidak ada sesi charging yang bisa diulas untuk lokasi ini. Ulasan hanya bisa diberikan sekali per sesi yang sudah selesai.');
        }

        return view('driver.reviews.create', compact('location', 'session'));
    }

    // Menyimpan review pengguna
    public function store(Request $request, $location_id)
    {
        $location = Location::findOrFail($location_id);

        $validated = $request->validate([
            'id_session' => 'required|integer',
            'rating_alat' => 'required|integer|min:1|max:5',
            'ulasan_alat' => 'nullable|string|max:1000',
            'rating_tempat' => 'required|integer|min:1|max:5',
            'ulasan_tempat' => 'nullable|string|max:1000',
            'foto_lokasi' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Memastikan sesi milik user, sudah selesai, di lokasi ini, dan belum diulas.
        $session = $this->reviewableSession($location_id, $validated['id_session']);

        if (! $session) {
            return redirect()
                ->route('spklu.show', $location->id_location)
                ->with('info', 'Sesi ini sudah Anda ulas atau tidak valid untuk diulas.');
        }

        $foto = null;

        if ($request->hasFile('foto_lokasi')) {
            $foto = $request->file('foto_lokasi')->store('reviews', 'public');
        }

        Review::create([
            'user_id' => Auth::id(),
            'id_location' => $location->id_location,
            'id_session' => $session->id_session,
            'rating_alat' => $validated['rating_alat'],
            'ulasan_alat' => $validated['ulasan_alat'] ?? null,
            'rating_tempat' => $validated['rating_tempat'],
            'ulasan_tempat' => $validated['ulasan_tempat'] ?? null,
            'foto_lokasi' => $foto,
        ]);

        return redirect()
            ->route('reviews.success')
            ->with('success', 'Terima kasih! Ulasan Anda berhasil dikirim.');
    }
}