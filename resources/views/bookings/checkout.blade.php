@extends('layouts.app')

@section('title', 'Checkout Booking')

@section('content')
@php
    $payment = $booking->payment;
    $isPaid = $payment?->status === \App\Models\Payment::STATUS_PAID;
@endphp

<section class="app-container py-10 lg:py-14">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="eyebrow">Checkout</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight">Invoice booking</h1>
            <p class="mt-2 text-[#6B7280]">Booking Code: <span class="font-black text-[#111827]">{{ $booking->booking_code }}</span></p>
        </div>
        <x-status-badge :status="$booking->status" />
    </div>

    <div class="mb-6 grid gap-3 md:grid-cols-4">
        @foreach(['1. Paket', '2. Kunjungan', '3. Data Pemesan', '4. Checkout'] as $step)
            <div class="rounded-[18px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-black text-[#007A5A]">
                {{ $step }}
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_380px]">
        <div class="space-y-6">
            <div class="surface-card overflow-hidden">
                <img src="{{ asset($booking->service->image_path ?: 'assets/images/destination-waterfall.jpg') }}" alt="Paket {{ $booking->service->name }}" loading="lazy" decoding="async" class="h-64 w-full object-cover">
                <div class="p-6 sm:p-7">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="eyebrow">Detail Paket</p>
                            <h2 class="mt-2 text-3xl font-black">{{ $booking->service->name }}</h2>
                        </div>
                        <span class="rounded-full bg-[#EEF3FF] px-4 py-2 text-xs font-black uppercase text-[#007A5A]">
                            {{ $booking->service->pricing_type === 'per_trip' ? 'Per Trip' : 'Per Peserta' }}
                        </span>
                    </div>
                    <p class="mt-5 text-sm leading-7 text-[#6B7280]">{{ $booking->service->description }}</p>
                </div>
            </div>

            <div class="surface-card p-6 sm:p-7">
                <h2 class="text-2xl font-black">Detail kunjungan</h2>
                <dl class="mt-6 grid gap-5 md:grid-cols-2">
                    <div>
                        <dt class="text-sm font-bold text-[#6B7280]">Tanggal kunjungan</dt>
                        <dd class="mt-1 text-lg font-black">{{ $booking->visit_date->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-[#6B7280]">Jumlah peserta</dt>
                        <dd class="mt-1 text-lg font-black">{{ $booking->participant_count }} orang</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-[#6B7280]">Nama pemesan</dt>
                        <dd class="mt-1 text-lg font-black">{{ $booking->contact_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-[#6B7280]">Kontak</dt>
                        <dd class="mt-1 text-lg font-black">{{ $booking->contact_phone }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-bold text-[#6B7280]">Email</dt>
                        <dd class="mt-1 text-lg font-black">{{ $booking->contact_email }}</dd>
                    </div>
                    @if($booking->notes)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-bold text-[#6B7280]">Catatan</dt>
                            <dd class="mt-1 rounded-[18px] bg-[#F7F8FC] p-4 text-sm leading-7 text-[#4B5563]">{{ $booking->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="surface-card p-6 sm:p-7">
                <h2 class="text-2xl font-black">Ketentuan kunjungan</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    @foreach([
                        ['Datang 15 menit lebih awal dari jadwal kunjungan.', 'clock'],
                        ['Tunjukkan e-ticket kepada petugas saat tiba di lokasi.', 'ticket'],
                        ['Gunakan alas kaki nyaman untuk jalur trekking basah.', 'map-pin'],
                    ] as [$text, $icon])
                        <div class="rounded-[18px] bg-[#EEF3FF] p-5">
                            <x-icon :name="$icon" class="h-6 w-6 text-[#007A5A]" />
                            <p class="mt-4 text-sm font-bold leading-6 text-[#374151]">{{ $text }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <aside class="surface-card h-fit p-6 sm:p-7 xl:sticky xl:top-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="eyebrow">Invoice</p>
                    <h2 class="mt-2 text-2xl font-black">Total pembayaran</h2>
                </div>
                <x-icon name="receipt" class="h-8 w-8 text-[#007A5A]" />
            </div>

            <div class="mt-6 space-y-4 text-sm">
                <div class="flex justify-between gap-4">
                    <span class="text-[#6B7280]">Harga paket</span>
                    <span class="font-black">Rp {{ number_format($booking->service->price, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-[#6B7280]">Jumlah peserta</span>
                    <span class="font-black">{{ $booking->participant_count }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-[#6B7280]">Subtotal</span>
                    <span class="font-black">Rp {{ number_format($booking->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-[#6B7280]">Biaya layanan</span>
                    <span class="font-black">Rp {{ number_format($booking->service_fee, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="mt-6 border-t border-[#E5EAF2] pt-6">
                <p class="text-sm font-bold text-[#6B7280]">Total</p>
                <p class="mt-2 text-4xl font-black text-[#007A5A]">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
            </div>

            <div class="mt-6 rounded-[20px] bg-[#EEF3FF] p-5">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-bold text-[#6B7280]">Payment</span>
                    <x-status-badge :status="$payment?->status ?? 'unpaid'" />
                </div>
                <p class="mt-4 text-sm leading-6 text-[#6B7280]">Pembayaran diproses melalui Midtrans Snap. Anda dapat memilih QRIS, virtual account, kartu, atau e-wallet sesuai kanal sandbox/production aktif.</p>
            </div>

            @if($isPaid && $booking->ticket)
                <a href="{{ route('my-bookings.ticket', $booking) }}" class="btn-primary mt-6 w-full py-4">
                    <x-icon name="ticket" class="h-5 w-5" /> Lihat E-Ticket
                </a>
            @elseif($booking->canPay() && $midtransReady)
                <button id="pay-button" class="btn-primary mt-6 w-full py-4" type="button">
                    <x-icon name="receipt" class="h-5 w-5" /> Bayar Sekarang
                </button>
                <p id="payment-message" class="mt-4 hidden rounded-[16px] bg-amber-50 p-4 text-sm font-bold leading-6 text-amber-700"></p>
            @elseif($booking->canPay())
                <button class="mt-6 inline-flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-[16px] bg-slate-300 px-5 py-4 text-sm font-extrabold text-slate-600" type="button" disabled>
                    <x-icon name="receipt" class="h-5 w-5" /> Payment Gateway Belum Aktif
                </button>
                <p class="mt-4 rounded-[16px] bg-amber-50 p-4 text-sm font-bold leading-6 text-amber-700">
                    Isi <span class="font-black">MIDTRANS_SERVER_KEY</span> dan <span class="font-black">MIDTRANS_CLIENT_KEY</span> di file .env, lalu jalankan <span class="font-black">php artisan config:clear</span>.
                </p>
            @else
                <a href="{{ route('my-bookings.show', $booking) }}" class="btn-secondary mt-6 w-full">Lihat Detail Pesanan</a>
            @endif
        </aside>
    </div>
</section>

@if($booking->canPay() && $midtransReady)
    <script src="{{ $snapJsUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
    <script>
        const payButton = document.getElementById('pay-button');
        const paymentMessage = document.getElementById('payment-message');

        function showPaymentMessage(message) {
            paymentMessage.textContent = message;
            paymentMessage.classList.remove('hidden');
        }

        payButton?.addEventListener('click', async () => {
            payButton.disabled = true;
            payButton.textContent = 'Memuat pembayaran...';

            try {
                const response = await fetch('{{ route('bookings.pay', $booking) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Gagal membuat transaksi pembayaran.');
                }

                if (!window.snap) {
                    throw new Error('Snap JS belum berhasil dimuat.');
                }

                window.snap.pay(data.snap_token, {
                    onSuccess: () => window.location.href = '{{ route('my-bookings.show', $booking) }}',
                    onPending: () => window.location.reload(),
                    onError: () => showPaymentMessage('Pembayaran gagal diproses. Silakan coba lagi.'),
                    onClose: () => showPaymentMessage('Pembayaran belum diselesaikan. Anda masih bisa membayar dari halaman checkout ini.'),
                });
            } catch (error) {
                showPaymentMessage(error.message);
            } finally {
                payButton.disabled = false;
                payButton.innerHTML = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 3v18l3-2 3 2 3-2 3 2 2-1.3V3Z"/><path d="M8 7h8M8 11h8M8 15h5"/></svg> Bayar Sekarang';
            }
        });
    </script>
@endif
@endsection
