<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGalleryRequest;
use App\Models\Gallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminGalleryController extends Controller
{
    public function index(): View
    {
        return view('admin.galleries.index', [
            'galleries' => Gallery::latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.galleries.create');
    }

    public function store(StoreGalleryRequest $request): RedirectResponse
    {
        Gallery::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'image_path' => $request->file('image')->store('galleries', 'public'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.galleries.index')->with('success', 'Foto galeri berhasil ditambahkan.');
    }

    public function edit(Gallery $gallery): View
    {
        return view('admin.galleries.edit', compact('gallery'));
    }

    public function update(StoreGalleryRequest $request, Gallery $gallery): RedirectResponse
    {
        $data = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            if (! Str::startsWith($gallery->image_path, ['http://', 'https://'])) {
                Storage::disk('public')->delete($gallery->image_path);
            }

            $data['image_path'] = $request->file('image')->store('galleries', 'public');
        }

        $gallery->update($data);

        return redirect()->route('admin.galleries.index')->with('success', 'Foto galeri berhasil diperbarui.');
    }

    public function destroy(Gallery $gallery): RedirectResponse
    {
        if (! Str::startsWith($gallery->image_path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($gallery->image_path);
        }

        $gallery->delete();

        return back()->with('success', 'Foto galeri berhasil dihapus.');
    }
}
