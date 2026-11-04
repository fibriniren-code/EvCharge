<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // Menampilkan seluruh ulasan pengguna
    public function index()
    {
        $avgEquipment = Review::avg('rating_alat') ?? 0;
        $avgFacility = Review::avg('rating_tempat') ?? 0;

        $reviews = Review::with(['user', 'location'])
            ->latest('created_at')
            ->paginate(10);

        return view('admin.reviews.index', compact(
            'reviews',
            'avgEquipment',
            'avgFacility'
        ));
    }

    // Memberikan tanggapan terhadap ulasan
    public function reply(Request $request, $id)
    {
        $request->validate([
            'admin_reply' => 'required|string|max:1000',
        ]);

        $review = Review::findOrFail($id);

        $review->update([
            'balasan_operator' => $request->admin_reply,
        ]);

        return back()->with(
            'success',
            'Tanggapan ulasan berhasil dikirim.'
        );
    }
}
