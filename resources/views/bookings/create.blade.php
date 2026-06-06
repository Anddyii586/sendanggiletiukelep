@extends('layouts.app')

@section('title', 'Booking Wizard')

@section('content')
@php
    $selectedId = old('package_id', $selectedService?->id);
@endphp

<section class="app-container py-10 lg:py-14">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="eyebrow">Booking Wizard</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight">Pesan paket wisata</h1>
            <p class="mt-2 max-w-2xl text-[#6B7280]">Pilih paket, tanggal kunjungan, jumlah peserta, lalu lengkapi data pemesan sebelum masuk ke checkout.</p>
        </div>
        <a href="{{ route('my-bookings.index') }}" class="btn-secondary">Pesanan Saya</a>
    </div>

    <div class="mb-6 grid gap-3 md:grid-cols-4">
        @foreach(['1. Paket', '2. Kunjungan', '3. Data Pemesan', '4. Checkout'] as $step)
            <div class="rounded-[18px] border {{ $loop->last ? 'border-[#E5EAF2] bg-white text-[#6B7280]' : 'border-emerald-200 bg-emerald-50 text-[#007A5A]' }} px-5 py-4 text-sm font-black">
                {{ $step }}
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('bookings.store') }}" class="grid gap-6 xl:grid-cols-[1fr_380px]">
        @csrf

        <div class="space-y-6">
            <div class="surface-card p-6 sm:p-7">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="eyebrow">Step 1</p>
                        <h2 class="mt-2 text-2xl font-black">Pilih paket wisata</h2>
                    </div>
                    <x-icon name="ticket" class="h-8 w-8 text-[#007A5A]" />
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-3">
                    @foreach($services as $service)
                        @php
                            $image = $service->image_path ?: 'assets/images/gallery-'.(($loop->iteration % 5) + 1).'.jpg';
                            $active = (int) $selectedId === (int) $service->id;
                        @endphp
                        <label class="group cursor-pointer overflow-hidden rounded-[22px] border {{ $active ? 'border-[#007A5A] bg-emerald-50 ring-4 ring-emerald-100' : 'border-[#E5EAF2] bg-white' }} transition hover:border-[#007A5A]">
                            <input type="radio" name="package_id" value="{{ $service->id }}" class="sr-only package-option"
                                data-name="{{ $service->name }}"
                                data-price="{{ $service->price }}"
                                data-pricing-type="{{ $service->pricing_type }}"
                                @checked($active)
                                required>
                            <img src="{{ asset($image) }}" alt="Paket {{ $service->name }}" loading="lazy" decoding="async" class="h-36 w-full object-cover">
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-black">{{ $service->name }}</h3>
                                    @if($service->is_featured)
                                        <span class="rounded-full bg-[#10B981] px-2 py-1 text-[10px] font-black uppercase text-white">Populer</span>
                                    @endif
                                </div>
                                <p class="mt-3 line-clamp-3 text-sm leading-6 text-[#6B7280]">{{ $service->description }}</p>
                                <p class="mt-4 text-xl font-black text-[#007A5A]">
                                    Rp {{ number_format($service->price, 0, ',', '.') }}
                                    <span class="text-xs font-bold text-[#6B7280]">/{{ $service->pricing_type === 'per_trip' ? 'trip' : 'orang' }}</span>
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('package_id') <p class="mt-3 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="surface-card p-6 sm:p-7">
                <p class="eyebrow">Step 2</p>
                <h2 class="mt-2 text-2xl font-black">Detail kunjungan</h2>
                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="form-label" for="visit_date">Tanggal kunjungan</label>
                        <input id="visit_date" name="visit_date" type="date" min="{{ now()->toDateString() }}" value="{{ old('visit_date', request('visit_date')) }}" class="form-input" required>
                        @error('visit_date') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label" for="participant_count">Jumlah peserta</label>
                        <input id="participant_count" name="participant_count" type="number" min="1" max="100" value="{{ old('participant_count', request('participant_count', 1)) }}" class="form-input" required>
                        @error('participant_count') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="surface-card p-6 sm:p-7">
                <p class="eyebrow">Step 3</p>
                <h2 class="mt-2 text-2xl font-black">Data pemesan</h2>
                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="form-label" for="contact_name">Nama pemesan</label>
                        <input id="contact_name" name="contact_name" value="{{ old('contact_name', auth()->user()->name) }}" class="form-input" required>
                        @error('contact_name') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label" for="contact_phone">Nomor WhatsApp</label>
                        <input id="contact_phone" name="contact_phone" value="{{ old('contact_phone', auth()->user()->phone) }}" class="form-input" required>
                        @error('contact_phone') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label" for="contact_email">Email</label>
                        <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', auth()->user()->email) }}" class="form-input" required>
                        @error('contact_email') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label" for="notes">Catatan kunjungan</label>
                        <textarea id="notes" name="notes" rows="4" class="form-input" placeholder="Opsional, misalnya kebutuhan khusus atau jam kedatangan.">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <aside class="surface-card h-fit p-6 sm:p-7 xl:sticky xl:top-8">
            <p class="eyebrow">Step 4</p>
            <h2 class="mt-2 text-2xl font-black">Ringkasan checkout</h2>
            <div class="mt-6 rounded-[20px] bg-[#EEF3FF] p-5">
                <p class="text-sm font-bold text-[#6B7280]">Paket terpilih</p>
                <p id="package_preview" class="mt-1 text-lg font-black">Belum dipilih</p>
                <div class="mt-5 grid gap-3 border-t border-[#DDE6F6] pt-5 text-sm">
                    <div class="flex items-center justify-between"><span class="font-bold text-[#6B7280]">Tanggal</span><span id="date_preview" class="font-black">-</span></div>
                    <div class="flex items-center justify-between"><span class="font-bold text-[#6B7280]">Peserta</span><span id="participant_preview" class="font-black">1 orang</span></div>
                    <div class="flex items-center justify-between"><span class="font-bold text-[#6B7280]">Subtotal</span><span id="subtotal_preview" class="font-black">Rp 0</span></div>
                </div>
            </div>
            <div class="mt-6">
                <p class="text-sm font-bold text-[#6B7280]">Total pembayaran</p>
                <p id="total_price_preview" class="mt-2 text-4xl font-black text-[#007A5A]">Rp 0</p>
            </div>
            <p class="mt-5 text-sm leading-6 text-[#6B7280]">Setelah konfirmasi, booking masuk status waiting payment dan pembayaran dilakukan via Midtrans Snap.</p>
            <button class="btn-primary mt-6 w-full py-4" type="submit">Lanjut ke Checkout</button>
        </aside>
    </form>
</section>

<script>
    const packageOptions = document.querySelectorAll('.package-option');
    const participantInput = document.getElementById('participant_count');
    const dateInput = document.getElementById('visit_date');
    const totalPreview = document.getElementById('total_price_preview');
    const subtotalPreview = document.getElementById('subtotal_preview');
    const packagePreview = document.getElementById('package_preview');
    const participantPreview = document.getElementById('participant_preview');
    const datePreview = document.getElementById('date_preview');

    function selectedPackage() {
        return [...packageOptions].find((option) => option.checked);
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
    }

    function updateTotal() {
        const selected = selectedPackage();
        const participants = Math.max(Number(participantInput.value || 1), 1);
        const price = Number(selected?.dataset?.price || 0);
        const pricingType = selected?.dataset?.pricingType || 'per_person';
        const subtotal = pricingType === 'per_trip' ? price : price * participants;

        packagePreview.textContent = selected?.dataset?.name || 'Belum dipilih';
        participantPreview.textContent = `${participants} orang`;
        datePreview.textContent = dateInput.value || '-';
        subtotalPreview.textContent = formatCurrency(subtotal);
        totalPreview.textContent = formatCurrency(subtotal);
    }

    packageOptions.forEach((option) => option.addEventListener('change', updateTotal));
    participantInput.addEventListener('input', updateTotal);
    dateInput.addEventListener('input', updateTotal);
    updateTotal();
</script>
@endsection
