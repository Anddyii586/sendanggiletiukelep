<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminAuditLog;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentLog;
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
            'pendingPayments' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            'paidPayments' => Payment::where('status', Payment::STATUS_PAID)->count(),
            'expiredBookings' => Booking::where('status', Booking::STATUS_EXPIRED)->count(),
            'todayBookings' => Booking::whereDate('created_at', today())->count(),
            'upcomingVisits' => Booking::whereDate('visit_date', '>=', today())
                ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED])
                ->count(),
            'totalReviews' => Review::count(),
            'latestBookings' => Booking::with(['user', 'service', 'payment', 'ticket'])->latest()->take(6)->get(),
            'waitingPaymentBookings' => Booking::with(['user', 'service', 'payment'])
                ->where('status', Booking::STATUS_WAITING_PAYMENT)
                ->latest()
                ->take(3)
                ->get(),
            'recentPayments' => Payment::with('booking.user', 'booking.service')->latest()->take(4)->get(),
            'recentPaidPayments' => Payment::with('booking.user', 'booking.service')
                ->where('status', Payment::STATUS_PAID)
                ->latest('paid_at')
                ->take(5)
                ->get(),
            'recentPaymentIssues' => PaymentLog::query()
                ->with('payment.booking')
                ->where(function ($query): void {
                    $query->where('signature_valid', false)
                        ->orWhereNotNull('error_message');
                })
                ->latest()
                ->take(5)
                ->get(),
            'recentAuditLogs' => AdminAuditLog::with('admin')->latest()->take(5)->get(),
            'latestServices' => Service::latest()->take(3)->get(),
        ]);
    }
}
