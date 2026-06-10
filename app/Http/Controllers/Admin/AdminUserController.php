<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $roles = ['user', 'admin'];
        $search = trim((string) $request->input('search'));

        $users = User::query()
            ->withCount([
                'bookings',
                'reviews',
                'bookings as paid_bookings_count' => fn ($query) => $query
                    ->whereHas('payment', fn ($paymentQuery) => $paymentQuery->where('status', Payment::STATUS_PAID)),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $like = "%{$search}%";

                $query->where(function ($query) use ($like): void {
                    $query->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                });
            })
            ->when(
                in_array($request->input('role'), $roles, true),
                fn ($query) => $query->where('role', $request->input('role'))
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function show(User $user): View
    {
        $bookings = $user->bookings()
            ->with(['service', 'payment', 'ticket'])
            ->latest()
            ->paginate(10, ['*'], 'bookings_page')
            ->withQueryString();
        $payments = Payment::query()
            ->with('booking.service')
            ->whereHas('booking', fn ($query) => $query->where('user_id', $user->id))
            ->latest()
            ->paginate(10, ['*'], 'payments_page')
            ->withQueryString();
        $reviews = $user->reviews()
            ->with('booking.service')
            ->latest()
            ->paginate(10, ['*'], 'reviews_page')
            ->withQueryString();

        return view('admin.users.show', [
            'user' => $user,
            'bookings' => $bookings,
            'payments' => $payments,
            'reviews' => $reviews,
            'stats' => [
                'total_bookings' => $user->bookings()->count(),
                'confirmed_bookings' => $user->bookings()->where('status', Booking::STATUS_CONFIRMED)->count(),
                'completed_bookings' => $user->bookings()->where('status', Booking::STATUS_COMPLETED)->count(),
                'total_paid_amount' => Payment::query()
                    ->whereHas('booking', fn ($query) => $query->where('user_id', $user->id))
                    ->where('status', Payment::STATUS_PAID)
                    ->sum('gross_amount'),
            ],
        ]);
    }
}
