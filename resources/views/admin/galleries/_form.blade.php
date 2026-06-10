@csrf
@isset($gallery)
    @method('PUT')
@endisset

<div class="space-y-6">
    <div>
        <label class="form-label" for="title">Judul foto</label>
        <input id="title" name="title" value="{{ old('title', $gallery->title ?? '') }}" class="form-input" required>
        @error('title') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="form-label" for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="5" class="form-input">{{ old('description', $gallery->description ?? '') }}</textarea>
        @error('description') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="form-label" for="image">Foto</label>
        <div class="rounded-[22px] border-2 border-dashed border-emerald-200 bg-emerald-50/50 p-6">
            <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-input bg-white" @if(!isset($gallery)) required @endif>
            <p class="mt-2 text-sm text-[#6B7280]">Format jpg, jpeg, png, atau webp. Maksimal 2MB.</p>
        </div>
        @error('image') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>
    @isset($gallery)
        <img src="{{ $gallery->image_url }}" alt="{{ $gallery->title }}" loading="lazy" decoding="async" class="h-52 rounded-[18px] object-cover">
    @endisset
    <label class="flex items-center gap-3 rounded-[16px] bg-[#EEF3FF] p-4 text-sm font-black text-[#374151]">
        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-[#007A5A]" @checked(old('is_active', $gallery->is_active ?? true))>
        Tampilkan di website publik
    </label>
    <div class="flex flex-wrap gap-3 pt-2">
        <button class="btn-primary" type="submit">Simpan</button>
        <a class="btn-secondary" href="{{ route('admin.galleries.index') }}">Batal</a>
    </div>
</div>
