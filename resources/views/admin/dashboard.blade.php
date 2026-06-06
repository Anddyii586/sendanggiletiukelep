@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
@php
    $preview = $latestBookings->first();
@endphp

<section>
    <div class="flex flex-wrap items-start justify-between gap-5">
        <div>
            <h1 class="text-4xl font-black tracking-tight">Dashboard Overview</h1>
            <p class="mt-2 text-lg text-[#6B7280]">Monitoring booking, payment gateway, dan performa paket wisata.</p>
        </div>
        <a href="{{ route('admin.services.create') }}" class="btn-primary"><x-icon name="plus" class="h-5 w-5" /> Tambah Paket</a>
    </div>

    <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-6">
        @foreach([
            ['Total Booking', $totalBookings, 'calendar', 'text-sky-600'],
            ['Waiting Payment', $totalWaitingPayment, 'receipt', 'text-orange-600'],
            ['Confirmed', $totalConfirmed, 'check', 'text-[#007A5A]'],
            ['Completed', $totalCompleted, 'ticket', 'text-teal-600'],
            ['Cancelled', $totalCancelled, 'x', 'text-red-600'],
            ['Revenue Paid', 'Rp '.number_format($totalRevenuePaid, 0, ',', '.'), 'receipt', 'text-[#007A5A]'],
        ] as [$label, $value, $icon, $color])
            <article class="surface-card p-6">
                <x-icon :name="$icon" class="h-7 w-7 {{ $color }}" />
                <p class="mt-8 text-sm font-black text-[#4B5563]">{{ $label }}</p>
                <p class="mt-3 text-3xl font-black">{{ $value }}</p>
            </article>
        @endforeach
    </div>

    <div class="mt-10 grid gap-6 xl:grid-cols-[1fr_380px]">
        <section class="surface-card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-4 p-7">
                <div>
                    <h2 class="text-2xl font-black">Recent Bookings</h2>
                    <p class="mt-1 text-sm text-[#6B7280]">Booking terbaru dari traveler.</p>
                </div>
                <a href="{{ route('admin.bookings.index') }}" class="text-sm font-black text-[#007A5A]">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="premium-table text-sm">
                    <thead>
                        <tr>
                            <th>Traveler</th>
                            <th>Booking</th>
                            <th>Paket</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestBookings->take(5) as $booking)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar-initial">{{ strtoupper(substr($booking->contact_name ?: $booking->user->name, 0, 1)) }}</div>
                                        <div><p class="font-black">{{ $booking->contact_name ?: $booking->user->name }}</p><p class="text-xs text-[#6B7280]">{{ $booking->contact_email ?: $booking->user->email }}</p></div>
                                    </div>
                                </td>
                                <td><p class="font-black">{{ $booking->booking_code }}</p><p class="text-xs text-[#6B7280]">{{ $booking->visit_date->format('d M Y') }}</p></td>
                                <td class="font-semibold">{{ $booking->service->name }}</td>
                                <td><x-status-badge :status="$booking->payment?->status ?? 'unpaid'" /></td>
                                <td><x-status-badge :status="$booking->status" /></td>
                                <td><a href="{{ route('admin.bookings.show', $booking) }}" class="text-[#007A5A]"><x-icon name="eye" class="h-5 w-5" /></a></td>
                            </tr>
                        @empty
                            <tr><td class="py-8 text-[#6B7280]" colspan="6">Belum ada booking.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="surface-card overflow-hidden">
            <div class="p-7">
                <h2 class="text-2xl font-black">Recent Payments</h2>
                <p class="mt-2 text-sm text-[#6B7280]">Status transaksi dari Midtrans.</p>
            </div>
            <div class="space-y-4 border-t border-[#E5EAF2] p-5">
                @forelse($recentPayments as $payment)
                    <a href="{{ route('admin.bookings.show', $payment->booking) }}" class="flex items-center gap-4 rounded-[16px] border border-[#E5EAF2] bg-[#F7F8FC] p-4 transition hover:border-[#007A5A]">
                        <div class="flex h-12 w-12 items-center justify-center rounded-[14px] bg-emerald-50 text-[#007A5A]"><x-icon name="receipt" class="h-6 w-6" /></div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-black">{{ $payment->order_id }}</p>
                            <p class="truncate text-xs text-[#6B7280]">Rp {{ number_format($payment->gross_amount, 0, ',', '.') }} - {{ $payment->booking?->contact_name ?? $payment->booking?->user?->name }}</p>
                        </div>
                        <x-status-badge :status="$payment->status" />
                    </a>
                @empty
                    <p class="rounded-[16px] bg-[#F7F8FC] p-4 text-sm text-[#6B7280]">Belum ada payment record.</p>
                @endforelse
            </div>
            <div class="bg-[#EEF3FF] p-5">
                <a href="{{ route('admin.bookings.index', ['status' => 'waiting_payment']) }}" class="btn-dark w-full">Lihat Waiting Payment</a>
            </div>
        </section>
    </div>

    @if($preview)
        <section class="surface-card mt-10 overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-4 bg-[#EEF3FF] p-7">
                <div>
                    <p class="eyebrow">Preview Detail</p>
                    <h2 class="mt-2 text-2xl font-black">Booking Code: {{ $preview->booking_code }}</h2>
                </div>
                <div class="flex flex-wrap gap-3">
                    @if($preview->status === \App\Models\Booking::STATUS_CONFIRMED)
                        <form method="POST" action="{{ route('admin.bookings.complete', $preview) }}">@csrf @method('PATCH')<button class="btn-dark" type="submit">Mark Completed</button></form>
                    @endif
                    @unless($preview->status === \App\Models\Booking::STATUS_COMPLETED)
                        <form method="POST" action="{{ route('admin.bookings.cancel', $preview) }}">@csrf @method('PATCH')<button class="btn-danger" type="submit">Cancel Booking</button></form>
                    @endunless
                </div>
            </div>
            <div class="grid gap-8 p-7 lg:grid-cols-[1fr_1fr]">
                <div>
                    <h3 class="text-sm font-black uppercase text-[#4B5563]">Traveler Data</h3>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div><p class="text-sm text-[#6B7280]">Nama Pemesan</p><p class="font-black">{{ $preview->contact_name ?: $preview->user->name }}</p></div>
                        <div><p class="text-sm text-[#6B7280]">Kontak</p><p class="font-black">{{ $preview->contact_phone ?: $preview->user->phone ?? '-' }}</p></div>
                    </div>
                    <div class="mt-6"><p class="text-sm text-[#6B7280]">Paket</p><p class="text-lg font-black text-[#007A5A]">{{ $preview->service->name }}</p></div>
                    <div class="mt-7 rounded-[18px] bg-[#F7F8FC] p-5">
                        <div class="flex justify-between text-sm"><span>Subtotal</span><span>Rp {{ number_format($preview->subtotal, 0, ',', '.') }}</span></div>
                        <div class="mt-3 flex justify-between text-sm"><span>Biaya layanan</span><span>Rp {{ number_format($preview->service_fee, 0, ',', '.') }}</span></div>
                        <div class="mt-4 flex justify-between border-t border-[#E5EAF2] pt-4 text-lg font-black"><span>Total</span><span class="text-[#007A5A]">Rp {{ number_format($preview->total_price, 0, ',', '.') }}</span></div>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-black uppercase text-[#4B5563]">Payment & Ticket</h3>
                    <div class="mt-5 rounded-[20px] bg-[#F7F8FC] p-5">
                        <div class="flex items-center justify-between gap-3"><span class="text-sm text-[#6B7280]">Payment</span><x-status-badge :status="$preview->payment?->status ?? 'unpaid'" /></div>
                        <div class="mt-4 flex items-center justify-between gap-3"><span class="text-sm text-[#6B7280]">Order ID</span><span class="text-right text-sm font-black">{{ $preview->payment?->order_id ?? '-' }}</span></div>
                        <div class="mt-4 flex items-center justify-between gap-3"><span class="text-sm text-[#6B7280]">Ticket</span><span class="text-right text-sm font-black">{{ $preview->ticket?->ticket_code ?? '-' }}</span></div>
                    </div>
                    <img src="{{ asset('assets/images/payment-proof-sample.jpg') }}" alt="Preview pembayaran gateway" loading="lazy" decoding="async" class="mt-5 h-64 w-full rounded-[18px] object-cover opacity-80">
                </div>
            </div>
        </section>
    @endif

    <section class="surface-card mt-10 overflow-hidden">
        <div class="flex items-center justify-between gap-4 p-7">
            <h2 class="text-2xl font-black">Kelola Paket</h2>
            <a href="{{ route('admin.services.create') }}" class="text-sm font-black text-[#007A5A]"><x-icon name="plus" class="inline h-4 w-4" /> Paket Baru</a>
        </div>
        <div class="overflow-x-auto">
            <table class="premium-table text-sm">
                <thead>
                    <tr>
                        <th>Paket</th>
                        <th>Harga</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestServices as $service)
                        <tr>
                            <td>
                                <div class="flex items-center gap-4">
                                    <img src="{{ asset($service->image_path ?: 'assets/images/gallery-2.jpg') }}" alt="Thumbnail {{ $service->name }}" loading="lazy" decoding="async" class="h-14 w-14 rounded-[12px] object-cover">
                                    <p class="font-black">{{ $service->name }}</p>
                                </div>
                            </td>
                            <td>Rp {{ number_format($service->price, 0, ',', '.') }}</td>
                            <td>{{ $service->pricing_type === 'per_trip' ? 'Per trip' : 'Per peserta' }}</td>
                            <td><span class="rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-black uppercase text-emerald-700">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td><a href="{{ route('admin.services.edit', $service) }}" class="text-slate-500"><x-icon name="edit" class="h-5 w-5" /></a></td>
                        </tr>
                    @empty
                        <tr><td class="py-8 text-[#6B7280]" colspan="5">Belum ada paket.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection
