@extends('layouts.admin')

@section('title', 'Detail Booking')

@section('content')
<section>
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="eyebrow">Booking Detail</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight">{{ $booking->booking_code }}</h1>
            <p class="mt-2 text-[#6B7280]">{{ $booking->service->name }} - {{ $booking->visit_date->format('d M Y') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-status-badge :status="$booking->status" />
            <x-status-badge :status="$booking->payment?->status ?? 'unpaid'" />
        </div>
    </div>

    <div class="surface-card overflow-hidden">
        <div class="flex flex-wrap items-start justify-between gap-4 bg-[#EEF3FF] p-6">
            <div>
                <p class="text-sm font-bold text-[#6B7280]">Order ID</p>
                <p class="mt-1 text-xl font-black">{{ $booking->payment?->order_id ?? '-' }}</p>
            </div>
            <div class="flex flex-wrap items-start gap-3">
                @if($booking->status === \App\Models\Booking::STATUS_CONFIRMED)
                    <form method="POST" action="{{ route('admin.bookings.complete', $booking) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn-dark" type="submit">Mark Completed</button>
                    </form>
                @endif
                @unless($booking->status === \App\Models\Booking::STATUS_COMPLETED)
                    <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" class="w-full max-w-md rounded-[18px] border border-red-200 bg-white p-4">
                        @csrf
                        @method('PATCH')
                        <label for="cancelled_reason" class="form-label">Alasan pembatalan</label>
                        <textarea id="cancelled_reason" name="cancelled_reason" rows="3" required minlength="5" class="form-input" placeholder="Tulis alasan minimal 5 karakter">{{ old('cancelled_reason') }}</textarea>
                        @error('cancelled_reason')
                            <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                        @enderror
                        @if($booking->payment?->status === \App\Models\Payment::STATUS_PAID)
                            <p class="mt-3 rounded-[14px] bg-amber-50 p-3 text-xs font-bold leading-5 text-amber-700">Payment sudah paid. Refund belum otomatis dan perlu diproses manual di luar sistem.</p>
                        @endif
                        <button class="btn-danger mt-4 w-full" type="submit">Cancel Booking</button>
                    </form>
                @endunless
            </div>
        </div>

        <div class="grid gap-8 p-7 xl:grid-cols-[1fr_1fr]">
            <div class="space-y-7">
                <div>
                    <h2 class="text-sm font-black uppercase text-[#4B5563]">Data Pemesan</h2>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div><p class="text-sm text-[#6B7280]">Nama</p><p class="font-black">{{ $booking->contact_name ?: $booking->user->name }}</p></div>
                        <div><p class="text-sm text-[#6B7280]">Email</p><p class="font-black">{{ $booking->contact_email ?: $booking->user->email }}</p></div>
                        <div><p class="text-sm text-[#6B7280]">Kontak</p><p class="font-black">{{ $booking->contact_phone ?: $booking->user->phone ?? '-' }}</p></div>
                        <div><p class="text-sm text-[#6B7280]">User Account</p><p class="font-black">{{ $booking->user->email }}</p></div>
                    </div>
                </div>

                <div>
                    <h2 class="text-sm font-black uppercase text-[#4B5563]">Data Booking</h2>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div><p class="text-sm text-[#6B7280]">Paket</p><p class="font-black text-[#007A5A]">{{ $booking->service->name }}</p></div>
                        <div><p class="text-sm text-[#6B7280]">Tanggal</p><p class="font-black">{{ $booking->visit_date->format('d M Y') }}</p></div>
                        <div><p class="text-sm text-[#6B7280]">Peserta</p><p class="font-black">{{ $booking->participant_count }} orang</p></div>
                        <div><p class="text-sm text-[#6B7280]">Expires</p><p class="font-black">{{ optional($booking->expires_at)->format('d M Y H:i') ?? '-' }}</p></div>
                    </div>
                    @if($booking->notes)
                        <div class="mt-5 rounded-[18px] bg-[#F7F8FC] p-5">
                            <p class="text-sm font-bold text-[#6B7280]">Catatan</p>
                            <p class="mt-2 text-sm leading-7 text-[#4B5563]">{{ $booking->notes }}</p>
                        </div>
                    @endif
                    @if($booking->cancelled_reason)
                        <div class="mt-5 rounded-[18px] bg-red-50 p-5">
                            <p class="text-sm font-bold text-red-700">Cancellation Reason</p>
                            <p class="mt-2 text-sm leading-7 text-red-700">{{ $booking->cancelled_reason }}</p>
                            <p class="mt-3 text-xs font-bold text-red-600">
                                {{ optional($booking->cancelled_at)->format('d M Y H:i') ?? '-' }}
                                @if($booking->cancelledBy)
                                    oleh {{ $booking->cancelledBy->name }}
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                <div class="rounded-[18px] bg-[#F7F8FC] p-5">
                    <div class="flex justify-between text-sm"><span>Subtotal</span><span>Rp {{ number_format($booking->subtotal, 0, ',', '.') }}</span></div>
                    <div class="mt-3 flex justify-between text-sm"><span>Biaya layanan</span><span>Rp {{ number_format($booking->service_fee, 0, ',', '.') }}</span></div>
                    <div class="mt-4 flex justify-between border-t border-[#E5EAF2] pt-4 text-lg font-black"><span>Total</span><span class="text-[#007A5A]">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span></div>
                </div>
            </div>

            <div class="space-y-7">
                <div>
                    <h2 class="text-sm font-black uppercase text-[#4B5563]">Data Payment</h2>
                    <div class="mt-5 rounded-[20px] bg-[#F7F8FC] p-5">
                        <dl class="grid gap-4">
                            <div class="flex justify-between gap-4"><dt class="text-sm text-[#6B7280]">Status</dt><dd><x-status-badge :status="$booking->payment?->status ?? 'unpaid'" /></dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-sm text-[#6B7280]">Transaction</dt><dd class="text-right text-sm font-black">{{ $booking->payment?->transaction_status ?? '-' }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-sm text-[#6B7280]">Payment Type</dt><dd class="text-right text-sm font-black">{{ $booking->payment?->payment_type ?? '-' }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-sm text-[#6B7280]">Fraud</dt><dd class="text-right text-sm font-black">{{ $booking->payment?->fraud_status ?? '-' }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-sm text-[#6B7280]">Gross Amount</dt><dd class="text-right text-sm font-black">Rp {{ number_format($booking->payment?->gross_amount ?? $booking->total_price, 0, ',', '.') }}</dd></div>
                            <div class="flex justify-between gap-4"><dt class="text-sm text-[#6B7280]">Paid At</dt><dd class="text-right text-sm font-black">{{ optional($booking->payment?->paid_at)->format('d M Y H:i') ?? '-' }}</dd></div>
                        </dl>
                    </div>
                </div>

                <div>
                    <h2 class="text-sm font-black uppercase text-[#4B5563]">E-Ticket</h2>
                    <div class="mt-5 rounded-[20px] border-2 border-dashed border-emerald-200 bg-emerald-50 p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm text-[#6B7280]">Ticket Code</p>
                                <p class="mt-1 text-xl font-black text-[#007A5A]">{{ $booking->ticket?->ticket_code ?? '-' }}</p>
                            </div>
                            @if($booking->ticket)
                                <x-status-badge :status="$booking->ticket->status" />
                            @endif
                        </div>
                        @if($booking->isTicketAvailable())
                            <a href="{{ route('admin.bookings.ticket', $booking) }}" class="btn-primary mt-5 w-full" target="_blank">Lihat E-Ticket</a>
                        @endif
                    </div>
                </div>

                <details class="rounded-[20px] border border-[#E5EAF2] bg-white p-5">
                    <summary class="cursor-pointer text-sm font-black uppercase text-[#4B5563]">Raw Response Midtrans</summary>
                    <pre class="mt-4 max-h-80 overflow-auto rounded-[16px] bg-[#0F1B2D] p-4 text-xs leading-6 text-slate-100">{{ json_encode($booking->payment?->raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '-' }}</pre>
                </details>

                @if($booking->review)
                    <div class="rounded-[18px] bg-[#EEF3FF] p-5">
                        <p class="font-black">Review</p>
                        <x-rating-stars :rating="$booking->review->rating" class="mt-3 h-4 w-4 text-[#007A5A]" />
                        @if($booking->review->comment)
                            <p class="mt-3 text-sm leading-7 text-[#6B7280]">{{ $booking->review->comment }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
