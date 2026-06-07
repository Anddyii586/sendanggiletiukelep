@extends(auth()->user()->isAdmin() ? 'layouts.admin' : 'layouts.app')

@section('title', 'E-Ticket')

@section('content')
<section class="{{ auth()->user()->isAdmin() ? '' : 'app-container py-10 lg:py-14' }}">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4 print:hidden">
        <div>
            <p class="eyebrow">E-Ticket</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight">Voucher Wisata</h1>
            <p class="mt-2 text-[#6B7280]">Tunjukkan halaman ini kepada petugas saat tiba di lokasi.</p>
        </div>
        <button onclick="window.print()" class="btn-primary" type="button"><x-icon name="receipt" class="h-5 w-5" /> Print</button>
    </div>

    <div class="mx-auto max-w-4xl overflow-hidden rounded-[28px] border border-[#E5EAF2] bg-white shadow-[0_24px_55px_rgba(15,27,45,.12)] print:shadow-none">
        <div class="bg-[#0F1B2D] p-8 text-white sm:p-10">
            <div class="flex flex-wrap items-start justify-between gap-5">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-[#67FFD0]">Sendang Gile & Tiu Kelep</p>
                    <h2 class="mt-3 text-3xl font-black">E-Ticket / Voucher Wisata</h2>
                </div>
                <x-status-badge :status="$booking->payment->status" />
            </div>
        </div>

        <div class="grid gap-8 p-8 sm:p-10 lg:grid-cols-[1fr_240px]">
            <div>
                <dl class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-bold text-[#6B7280]">Booking Code</dt>
                        <dd class="mt-1 text-2xl font-black">{{ $booking->booking_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-[#6B7280]">Ticket Code</dt>
                        <dd class="mt-1 text-2xl font-black text-[#007A5A]">{{ $booking->ticket->ticket_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-[#6B7280]">Nama Pemesan</dt>
                        <dd class="mt-1 text-lg font-black">{{ $booking->contact_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-[#6B7280]">Paket</dt>
                        <dd class="mt-1 text-lg font-black">{{ $booking->service->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-[#6B7280]">Tanggal Kunjungan</dt>
                        <dd class="mt-1 text-lg font-black">{{ $booking->visit_date->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-[#6B7280]">Jumlah Peserta</dt>
                        <dd class="mt-1 text-lg font-black">{{ $booking->participant_count }} orang</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-[#6B7280]">Total Pembayaran</dt>
                        <dd class="mt-1 text-lg font-black text-[#007A5A]">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-[#6B7280]">Status Ticket</dt>
                        <dd class="mt-2"><x-status-badge :status="$booking->ticket->status" /></dd>
                    </div>
                </dl>

                <div class="mt-8 rounded-[20px] bg-[#EEF3FF] p-6">
                    <h3 class="font-black">Instruksi</h3>
                    <p class="mt-3 text-sm leading-7 text-[#4B5563]">Tunjukkan e-ticket ini kepada petugas saat tiba di lokasi. Pastikan nama pemesan dan tanggal kunjungan sesuai dengan jadwal Anda.</p>
                </div>
            </div>

            <aside class="flex flex-col items-center justify-center rounded-[24px] border-2 border-dashed border-emerald-200 bg-emerald-50 p-6 text-center">
                <div data-testid="ticket-qr" class="rounded-[18px] bg-white p-3 shadow-[0_16px_32px_rgba(15,27,45,.08)] [&_svg]:h-44 [&_svg]:w-44">
                    {!! $booking->ticket->qrSvg(176) !!}
                </div>
                <p class="mt-5 text-xs font-black uppercase tracking-[0.16em] text-[#007A5A]">QR Ticket</p>
                <p class="mt-2 text-sm leading-6 text-[#6B7280]">{{ $booking->ticket->ticket_code }}</p>
            </aside>
        </div>
    </div>

    <div class="mt-8 flex flex-wrap justify-center gap-3 print:hidden">
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.bookings.show', $booking) }}" class="btn-secondary">Kembali ke Detail Admin</a>
        @else
            <a href="{{ route('my-bookings.index') }}" class="btn-secondary">Kembali ke Pesanan Saya</a>
            <a href="{{ route('my-bookings.show', $booking) }}" class="btn-dark">Detail Pesanan</a>
        @endif
    </div>
</section>
@endsection
