@extends('layouts.admin')

@section('title', 'Detail User')

@section('content')
<section>
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="eyebrow">User Detail</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight">{{ $user->name }}</h1>
            <p class="mt-2 text-[#6B7280]">{{ $user->email }} - {{ ucfirst($user->role) }}</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Kembali</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[360px_1fr]">
        <aside class="surface-card p-6">
            <div class="avatar-initial h-16 w-16 text-lg">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <dl class="mt-6 grid gap-4 text-sm">
                <div><dt class="text-[#6B7280]">Name</dt><dd class="mt-1 font-black">{{ $user->name }}</dd></div>
                <div><dt class="text-[#6B7280]">Email</dt><dd class="mt-1 font-black">{{ $user->email }}</dd></div>
                <div><dt class="text-[#6B7280]">Phone</dt><dd class="mt-1 font-black">{{ $user->phone ?? '-' }}</dd></div>
                <div><dt class="text-[#6B7280]">Role</dt><dd class="mt-1 font-black">{{ ucfirst($user->role) }}</dd></div>
                <div><dt class="text-[#6B7280]">Created At</dt><dd class="mt-1 font-black">{{ $user->created_at->format('d M Y H:i') }}</dd></div>
            </dl>
        </aside>

        <div class="space-y-6">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach([
                    ['Total Bookings', $stats['total_bookings'], 'calendar', 'text-sky-600'],
                    ['Confirmed', $stats['confirmed_bookings'], 'check', 'text-[#007A5A]'],
                    ['Completed', $stats['completed_bookings'], 'ticket', 'text-teal-600'],
                    ['Total Paid Amount', 'Rp '.number_format($stats['total_paid_amount'], 0, ',', '.'), 'receipt', 'text-[#007A5A]'],
                ] as [$label, $value, $icon, $color])
                    <article class="surface-card p-6">
                        <x-icon :name="$icon" class="h-7 w-7 {{ $color }}" />
                        <p class="mt-8 text-sm font-black text-[#4B5563]">{{ $label }}</p>
                        <p class="mt-3 text-2xl font-black">{{ $value }}</p>
                    </article>
                @endforeach
            </div>

            <section class="surface-card overflow-hidden">
                <div class="p-6">
                    <h2 class="text-2xl font-black">Riwayat Booking</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="premium-table text-sm">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>Package</th>
                                <th>Visit</th>
                                <th>Participants</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                                <tr>
                                    <td class="font-black">{{ $booking->booking_code }}</td>
                                    <td>{{ $booking->service?->name ?? '-' }}</td>
                                    <td>{{ optional($booking->visit_date)->format('d M Y') }}</td>
                                    <td>{{ $booking->participant_count }}</td>
                                    <td><x-status-badge :status="$booking->payment?->status ?? 'unpaid'" /></td>
                                    <td><x-status-badge :status="$booking->status" /></td>
                                    <td><a class="text-[#007A5A]" href="{{ route('admin.bookings.show', $booking) }}"><x-icon name="eye" class="h-5 w-5" /></a></td>
                                </tr>
                            @empty
                                <tr><td class="py-8 text-[#6B7280]" colspan="7">Belum ada booking.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6">{{ $bookings->links() }}</div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <div class="surface-card overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-2xl font-black">Riwayat Payment</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="premium-table text-sm">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Paid At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td class="font-black">{{ $payment->order_id ?? '-' }}</td>
                                        <td><x-status-badge :status="$payment->status" /></td>
                                        <td>Rp {{ number_format($payment->gross_amount, 0, ',', '.') }}</td>
                                        <td>{{ optional($payment->paid_at)->format('d M Y H:i') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="py-8 text-[#6B7280]" colspan="4">Belum ada payment.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6">{{ $payments->links() }}</div>
                </div>

                <div class="surface-card overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-2xl font-black">Riwayat Review</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="premium-table text-sm">
                            <thead>
                                <tr>
                                    <th>Package</th>
                                    <th>Rating</th>
                                    <th>Visible</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reviews as $review)
                                    <tr>
                                        <td>{{ $review->booking?->service?->name ?? '-' }}</td>
                                        <td class="font-black">{{ $review->rating }}/5</td>
                                        <td>{{ $review->is_visible ? 'Yes' : 'No' }}</td>
                                        <td>{{ $review->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="py-8 text-[#6B7280]" colspan="4">Belum ada review.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6">{{ $reviews->links() }}</div>
                </div>
            </section>
        </div>
    </div>
</section>
@endsection
