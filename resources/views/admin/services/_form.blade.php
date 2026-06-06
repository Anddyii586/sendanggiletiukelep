@csrf
@isset($service)
    @method('PUT')
@endisset

<div class="space-y-6">
    <div>
        <label class="form-label" for="name">Nama layanan</label>
        <input id="name" name="name" value="{{ old('name', $service->name ?? '') }}" class="form-input" required>
        @error('name') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="form-label" for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="5" class="form-input">{{ old('description', $service->description ?? '') }}</textarea>
        @error('description') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="form-label" for="price">Harga</label>
        <input id="price" name="price" type="number" min="0" step="1000" value="{{ old('price', $service->price ?? '') }}" class="form-input" required>
        @error('price') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <label class="form-label" for="pricing_type">Tipe harga</label>
            <select id="pricing_type" name="pricing_type" class="form-input" required>
                <option value="per_person" @selected(old('pricing_type', $service->pricing_type ?? 'per_person') === 'per_person')>Per peserta</option>
                <option value="per_trip" @selected(old('pricing_type', $service->pricing_type ?? 'per_person') === 'per_trip')>Per trip</option>
            </select>
            @error('pricing_type') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label" for="sort_order">Urutan tampil</label>
            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $service->sort_order ?? '') }}" class="form-input" placeholder="Opsional">
            @error('sort_order') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
    <label class="flex items-center gap-3 rounded-[16px] bg-emerald-50 p-4 text-sm font-black text-[#374151]">
        <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 rounded border-slate-300 text-[#007A5A]" @checked(old('is_featured', $service->is_featured ?? false))>
        Jadikan paket unggulan
    </label>
    <label class="flex items-center gap-3 rounded-[16px] bg-[#EEF3FF] p-4 text-sm font-black text-[#374151]">
        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-[#007A5A]" @checked(old('is_active', $service->is_active ?? true))>
        Aktifkan layanan
    </label>
    <div class="flex flex-wrap gap-3 pt-2">
        <button class="btn-primary" type="submit">Simpan</button>
        <a class="btn-secondary" href="{{ route('admin.services.index') }}">Batal</a>
    </div>
</div>
