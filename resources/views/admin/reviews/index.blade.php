@extends('layouts.admin')

@section('title', 'Kelola Review')

@section('content')
<section>
    <div class="mb-8">
        <h1 class="text-4xl font-black tracking-tight">Kelola Review</h1>
        <p class="mt-2 text-[#6B7280]">Atur visibilitas review yang tampil di halaman publik.</p>
    </div>

    <div class="surface-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="premium-table text-sm">
                <thead class="table-head">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Layanan</th>
                        <th class="px-6 py-4">Rating</th>
                        <th class="px-6 py-4">Komentar</th>
                        <th class="px-6 py-4">Visibilitas</th>
                        <th class="px-6 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5EAF2]">
                    @forelse($reviews as $review)
                        <tr>
                            <td class="px-6 py-5 font-black">{{ $review->user->name }}</td>
                            <td class="px-6 py-5">{{ $review->booking->service->name ?? '-' }}</td>
                            <td class="px-6 py-5"><x-rating-stars :rating="$review->rating" class="h-4 w-4 text-[#007A5A]" /></td>
                            <td class="max-w-md px-6 py-5 text-[#6B7280]">{{ $review->comment }}</td>
                            <td class="px-6 py-5"><span class="rounded-full {{ $review->is_visible ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1 text-[11px] font-black uppercase">{{ $review->is_visible ? 'Tampil' : 'Tersembunyi' }}</span></td>
                            <td class="px-6 py-5">
                                <form method="POST" action="{{ route('admin.reviews.visibility', $review) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn-secondary px-4 py-2" type="submit">{{ $review->is_visible ? 'Sembunyikan' : 'Tampilkan' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-6 py-8 text-[#6B7280]" colspan="6">Belum ada review.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">{{ $reviews->links() }}</div>
</section>
@endsection
