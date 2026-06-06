@extends('layouts.admin')

@section('title', 'Layanan Wisata')

@section('content')
<section>
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-4xl font-black tracking-tight">Kelola Layanan</h1>
            <p class="mt-2 text-[#6B7280]">Kelola tiket, paket wisata, dan layanan guide.</p>
        </div>
        <a href="{{ route('admin.services.create') }}" class="btn-primary"><x-icon name="plus" class="h-5 w-5" /> Tambah Layanan</a>
    </div>

    <div class="surface-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="premium-table text-sm">
                <thead class="table-head">
                    <tr>
                        <th class="px-6 py-4">Layanan</th>
                        <th class="px-6 py-4">Harga</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5EAF2]">
                    @forelse($services as $service)
                        <tr>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <img src="{{ asset('assets/images/gallery-2.jpg') }}" alt="Thumbnail layanan wisata" loading="lazy" decoding="async" class="h-16 w-16 rounded-[14px] object-cover">
                                    <div><p class="font-black">{{ $service->name }}</p><p class="mt-1 max-w-lg text-sm text-[#6B7280]">{{ $service->description }}</p></div>
                                </div>
                            </td>
                            <td class="px-6 py-5 font-bold">Rp {{ number_format($service->price, 0, ',', '.') }} / pax</td>
                            <td class="px-6 py-5"><span class="rounded-full {{ $service->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1 text-[11px] font-black uppercase">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="px-6 py-5">
                                <div class="flex gap-3">
                                    <a class="text-slate-500 hover:text-[#007A5A]" href="{{ route('admin.services.edit', $service) }}"><x-icon name="edit" class="h-5 w-5" /></a>
                                    <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Hapus layanan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-slate-400 hover:text-red-600" type="submit"><x-icon name="trash" class="h-5 w-5" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-6 py-8 text-[#6B7280]" colspan="4">Belum ada layanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">{{ $services->links() }}</div>
</section>
@endsection
