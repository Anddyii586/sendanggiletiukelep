@extends('layouts.admin')

@section('title', 'Kelola Galeri')

@section('content')
<section>
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-4xl font-black tracking-tight">Kelola Galeri</h1>
            <p class="mt-2 text-[#6B7280]">Kelola foto yang tampil di halaman publik.</p>
        </div>
        <a href="{{ route('admin.galleries.create') }}" class="btn-primary"><x-icon name="plus" class="h-5 w-5" /> Tambah Foto</a>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse($galleries as $gallery)
            <article class="surface-card overflow-hidden">
                <img src="{{ $gallery->image_url }}" alt="{{ $gallery->title }}" loading="lazy" decoding="async" class="h-60 w-full object-cover">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black">{{ $gallery->title }}</h2>
                            <p class="mt-2 text-sm leading-6 text-[#6B7280]">{{ $gallery->description }}</p>
                        </div>
                        <span class="rounded-full {{ $gallery->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1 text-[11px] font-black uppercase">{{ $gallery->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <div class="mt-5 flex gap-3">
                        <a class="btn-secondary px-4 py-2" href="{{ route('admin.galleries.edit', $gallery) }}"><x-icon name="edit" class="h-4 w-4" /> Edit</a>
                        <form method="POST" action="{{ route('admin.galleries.destroy', $gallery) }}" onsubmit="return confirm('Hapus foto ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn-danger px-4 py-2" type="submit"><x-icon name="trash" class="h-4 w-4" /> Hapus</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="surface-card p-8 text-[#6B7280]">Belum ada galeri.</div>
        @endforelse
    </div>

    <div class="mt-8">{{ $galleries->links() }}</div>
</section>
@endsection
