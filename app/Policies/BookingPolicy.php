<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || (int) $booking->user_id === (int) $user->id;
    }

    public function uploadPayment(User $user, Booking $booking): bool
    {
        return false;
    }

    public function createReview(User $user, Booking $booking): bool
    {
        return ! $user->isAdmin()
            && (int) $booking->user_id === (int) $user->id
            && $booking->canBeReviewed();
    }

    public function pay(User $user, Booking $booking): bool
    {
        return ! $user->isAdmin()
            && (int) $booking->user_id === (int) $user->id
            && $booking->canPay();
    }

    public function viewTicket(User $user, Booking $booking): bool
    {
        return ($user->isAdmin() || (int) $booking->user_id === (int) $user->id)
            && $booking->isTicketAvailable();
    }
}
