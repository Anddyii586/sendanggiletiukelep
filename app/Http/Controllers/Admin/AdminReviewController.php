<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\AdminAuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function visibility(Request $request, Review $review, AdminAuditLogService $auditLog): RedirectResponse
    {
        $previousVisibility = $review->is_visible;

        $review->update([
            'is_visible' => ! $review->is_visible,
        ]);

        $auditLog->log(
            $request->user(),
            'review.visibility_toggled',
            $review,
            'Visibilitas review diperbarui.',
            [
                'previous_is_visible' => $previousVisibility,
                'new_is_visible' => $review->is_visible,
            ]
        );

        return back()->with('success', 'Visibilitas review berhasil diperbarui.');
    }
}
