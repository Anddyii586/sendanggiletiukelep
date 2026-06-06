@extends('layouts.traveler')

@section('title', 'Upload Bukti Pembayaran')

@section('content')
<section>
    <div class="mb-8">
        <p class="eyebrow">Pembayaran</p>
        <h1 class="mt-3 text-4xl font-black tracking-tight">Upload Bukti Pembayaran</h1>
        <p class="mt-2 text-[#6B7280]">Booking ID: #BK-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_380px]">
        <form method="POST" action="{{ route('payments.store', $booking) }}" enctype="multipart/form-data" class="surface-card p-7">
            @csrf
            <div class="rounded-[22px] border-2 border-dashed border-emerald-200 bg-emerald-50/50 p-10 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-[#007A5A] shadow-sm">
                    <x-icon name="upload" class="h-8 w-8" />
                </div>
                <label class="mt-5 block text-lg font-black" for="proof">Pilih file bukti pembayaran</label>
                <p class="mt-2 text-sm text-[#6B7280]">Format jpg, jpeg, png, atau pdf. Maksimal 2MB.</p>
                <input id="proof" name="proof" type="file" accept=".jpg,.jpeg,.png,.pdf" class="form-input mt-6" required>
                @error('proof') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
            </div>
            <button class="btn-primary mt-6 w-full py-4" type="submit">Upload Pembayaran</button>
        </form>

        <aside class="space-y-6">
            <div class="surface-card p-7">
                <h2 class="text-xl font-black">Info Booking</h2>
                <div class="mt-5 space-y-4 text-sm">
                    <div class="flex justify-between gap-4"><span class="text-[#6B7280]">Layanan</span><strong>{{ $booking->service->name }}</strong></div>
                    <div class="flex justify-between gap-4"><span class="text-[#6B7280]">Tanggal</span><strong>{{ $booking->visit_date->format('d M Y') }}</strong></div>
                    <div class="flex justify-between gap-4"><span class="text-[#6B7280]">Peserta</span><strong>{{ $booking->participant_count }} orang</strong></div>
                    <div class="border-t border-[#E5EAF2] pt-4">
                        <span class="text-[#6B7280]">Total pembayaran</span>
                        <p class="mt-1 text-3xl font-black text-[#007A5A]">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="surface-card p-7">
                <h2 class="text-xl font-black">Instruksi Transfer</h2>
                <p class="mt-3 text-sm leading-7 text-[#6B7280]">Transfer sesuai total pembayaran ke rekening pengelola, lalu unggah bukti transfer pada form ini.</p>
                <div class="mt-5 rounded-[18px] bg-[#EEF3FF] p-5 text-sm">
                    <p class="font-black">Bank NTB Syariah</p>
                    <p class="mt-1 text-[#6B7280]">1234567890 a.n. Pengelola Wisata Senaru</p>
                </div>
            </div>
        </aside>
    </div>
</section>
@endsection
