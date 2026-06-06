<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $baseQuery = Booking::query()->whereBelongsTo($request->user());

        return view('user.dashboard', [
            'totalBookings' => (clone $baseQuery)->count(),
            'waitingPaymentBookings' => (clone $baseQuery)->where('status', Booking::STATUS_WAITING_PAYMENT)->count(),
            'confirmedBookings' => (clone $baseQuery)->where('status', Booking::STATUS_CONFIRMED)->count(),
            'completedBookings' => (clone $baseQuery)->where('status', Booking::STATUS_COMPLETED)->count(),
            'latestBookings' => (clone $baseQuery)
                ->with(['service', 'payment', 'ticket', 'review'])
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
