<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(): View
    {
        return view('admin.reviews.index', [
            'reviews' => Review::with(['user', 'booking.service'])
                ->latest()
                ->paginate(10),
        ]);
    }

    public function visibility(Review $review): RedirectResponse
    {
        $review->update([
            'is_visible' => ! $review->is_visible,
        ]);

        return back()->with('success', 'Visibilitas review berhasil diperbarui.');
    }
}
