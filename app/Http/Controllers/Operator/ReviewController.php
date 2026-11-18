<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** Penanganan ulasan & keluhan pengemudi (FR-11). filter: all | unreplied | complaints */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'unreplied');

        $reviews = Review::with(['user', 'location'])
            ->when($filter === 'unreplied', fn ($q) => $q->whereNull('balasan_operator'))
            ->when($filter === 'complaints', fn ($q) => $q->where(
                fn ($w) => $w->where('rating_alat', '<=', 2)->orWhere('rating_tempat', '<=', 2)
            ))
            ->latest('id_review')
            ->paginate(8)
            ->withQueryString();

        return view('operator.reviews', [
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
        $data = $request->validate([
            'balasan' => ['required', 'string', 'max:1000'],
        ]);

        $review->update(['balasan_operator' => $data['balasan']]);

        return back()->with('success', 'Tanggapan terkirim ke pengemudi.');
    }
}