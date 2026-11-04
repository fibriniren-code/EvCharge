<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        // Rekapitulasi rata-rata rating terpisah
        $avgEquipment = Review::avg('equipment_rating');
        $avgFacility  = Review::avg('facility_rating');

        $reviews = Review::with(['user', 'location'])->latest()->paginate(10);

        return view('admin.reviews.index', compact('reviews', 'avgEquipment', 'avgFacility'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'admin_reply' => 'required|string',
        ]);

        $review = Review::findOrFail($id);
        $review->update([
            'admin_reply' => $request->admin_reply,
        ]);

        return back()->with('success', 'Tanggapan ulasan berhasil dikirim.');
    }
}