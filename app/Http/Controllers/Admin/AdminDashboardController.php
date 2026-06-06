<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'totalBookings' => Booking::count(),
            'totalWaitingPayment' => Booking::where('status', Booking::STATUS_WAITING_PAYMENT)->count(),
            'totalConfirmed' => Booking::where('status', Booking::STATUS_CONFIRMED)->count(),
            'totalCompleted' => Booking::where('status', Booking::STATUS_COMPLETED)->count(),
            'totalCancelled' => Booking::where('status', Booking::STATUS_CANCELLED)->count(),
            'totalRevenuePaid' => Payment::where('status', Payment::STATUS_PAID)->sum('gross_amount'),
            'totalReviews' => Review::count(),
            'latestBookings' => Booking::with(['user', 'service', 'payment', 'ticket'])->latest()->take(6)->get(),
            'waitingPaymentBookings' => Booking::with(['user', 'service', 'payment'])
                ->where('status', Booking::STATUS_WAITING_PAYMENT)
                ->latest()
                ->take(3)
                ->get(),
            'recentPayments' => Payment::with('booking.user', 'booking.service')->latest()->take(4)->get(),
            'latestServices' => Service::latest()->take(3)->get(),
        ]);
    }
}
