@extends('layouts.admin')

@section('title', 'Tambah Foto')

@section('content')
<section class="max-w-3xl">
    <div class="mb-8">
        <h1 class="text-4xl font-black tracking-tight">Tambah Foto</h1>
        <p class="mt-2 text-[#6B7280]">Upload foto baru untuk galeri wisata.</p>
    </div>
    <form method="POST" action="{{ route('admin.galleries.store') }}" enctype="multipart/form-data" class="surface-card p-7">
        @include('admin.galleries._form')
    </form>
</section>
@endsection
