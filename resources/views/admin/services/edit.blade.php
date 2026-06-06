@extends('layouts.admin')

@section('title', 'Edit Layanan')

@section('content')
<section class="max-w-3xl">
    <div class="mb-8">
        <h1 class="text-4xl font-black tracking-tight">Edit Layanan</h1>
        <p class="mt-2 text-[#6B7280]">Perbarui informasi layanan wisata.</p>
    </div>
    <form method="POST" action="{{ route('admin.services.update', $service) }}" class="surface-card p-7">
        @include('admin.services._form')
    </form>
</section>
@endsection
