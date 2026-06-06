@extends('layouts.admin')

@section('title', 'Tambah Layanan')

@section('content')
<section class="max-w-3xl">
    <div class="mb-8">
        <h1 class="text-4xl font-black tracking-tight">Tambah Layanan</h1>
        <p class="mt-2 text-[#6B7280]">Tambahkan layanan tiket atau guide baru.</p>
    </div>
    <form method="POST" action="{{ route('admin.services.store') }}" class="surface-card p-7">
        @include('admin.services._form')
    </form>
</section>
@endsection
