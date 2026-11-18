<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $reviews = Review::with(['user', 'location'])
            ->when($filter === 'unreplied', fn ($q) => $q->whereNull('balasan_operator'))
            ->latest('id_review')
            ->paginate(8)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'filter' => $filter,
            'avgAlat' => (float) Review::avg('rating_alat'),
            'avgTempat' => (float) Review::avg('rating_tempat'),
            'total' => Review::count(),
            'unreplied' => Review::whereNull('balasan_operator')->count(),
        ]);
    }

    public function reply(Request $request, Review $review)
    {
        // Nama field "balasan" (sebelumnya "admin_reply"); sesuaikan bila form lamamu masih memakai nama lama
        $data = $request->validate([
            'balasan' => ['required', 'string', 'max:1000'],
        ]);

        $review->update(['balasan_operator' => $data['balasan']]);

        return back()->with('success', 'Tanggapan ulasan berhasil dikirim.');
    }

    // Moderasi: hapus ulasan yang tidak pantas
    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Ulasan dihapus.');
    }
}