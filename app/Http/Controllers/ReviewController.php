<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        return view('public.reviews', [
            'reviews' => Review::visible()
                ->with(['user', 'booking.service'])
                ->latest()
                ->paginate(10),
        ]);
    }

    public function create(Booking $booking): View
    {
        Gate::authorize('createReview', $booking);

        return view('reviews.create', [
            'booking' => $booking->load('service'),
        ]);
    }

    public function store(StoreReviewRequest $request, Booking $booking): RedirectResponse
    {
        $data = $request->validated();

        Review::create([
            'booking_id' => $booking->id,
            'user_id' => $request->user()->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_visible' => true,
        ]);

        return redirect()
            ->route('my-bookings.show', $booking)
            ->with('success', 'Review berhasil dikirim.');
    }
}
