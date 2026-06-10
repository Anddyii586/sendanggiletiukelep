<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGalleryRequest;
use App\Models\Gallery;
use App\Services\AdminAuditLogService;
use App\Services\CloudinaryImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

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

    public function store(
        StoreGalleryRequest $request,
        AdminAuditLogService $auditLog,
        CloudinaryImageService $cloudinaryImageService
    ): RedirectResponse {
        try {
            $uploadedImage = $cloudinaryImageService->uploadGalleryImage($request->file('image'));
        } catch (RuntimeException $exception) {
            return back()->withErrors(['image' => $exception->getMessage()])->withInput();
        }

        $gallery = Gallery::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'image_path' => $uploadedImage['secure_url'],
            'cloudinary_public_id' => $uploadedImage['public_id'],
            'cloudinary_secure_url' => $uploadedImage['secure_url'],
            'storage_disk' => 'cloudinary',
            'is_active' => $request->boolean('is_active'),
        ]);

        $auditLog->log(
            $request->user(),
            'gallery.created',
            $gallery,
            "Foto galeri {$gallery->title} dibuat.",
            ['title' => $gallery->title]
        );

        return redirect()->route('admin.galleries.index')->with('success', 'Foto galeri berhasil ditambahkan.');
    }

    public function edit(Gallery $gallery): View
    {
        return view('admin.galleries.edit', compact('gallery'));
    }

    public function update(
        StoreGalleryRequest $request,
        Gallery $gallery,
        AdminAuditLogService $auditLog,
        CloudinaryImageService $cloudinaryImageService
    ): RedirectResponse {
        $before = $this->auditFields($gallery);
        $data = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            try {
                $uploadedImage = $cloudinaryImageService->uploadGalleryImage($request->file('image'));
            } catch (RuntimeException $exception) {
                return back()->withErrors(['image' => $exception->getMessage()])->withInput();
            }

            if (filled($gallery->cloudinary_public_id)) {
                $cloudinaryImageService->deleteImage($gallery->cloudinary_public_id);
            } else {
                $this->deleteLegacyLocalImage($gallery->image_path, $gallery->storage_disk);
            }

            $data['image_path'] = $uploadedImage['secure_url'];
            $data['cloudinary_public_id'] = $uploadedImage['public_id'];
            $data['cloudinary_secure_url'] = $uploadedImage['secure_url'];
            $data['storage_disk'] = 'cloudinary';
        }

        $gallery->update($data);

        $auditLog->log(
            $request->user(),
            'gallery.updated',
            $gallery,
            "Foto galeri {$gallery->title} diperbarui.",
            [
                'before' => $before,
                'after' => $this->auditFields($gallery),
            ]
        );

        return redirect()->route('admin.galleries.index')->with('success', 'Foto galeri berhasil diperbarui.');
    }

    public function destroy(
        Request $request,
        Gallery $gallery,
        AdminAuditLogService $auditLog,
        CloudinaryImageService $cloudinaryImageService
    ): RedirectResponse {
        $galleryTitle = $gallery->title;

        if (filled($gallery->cloudinary_public_id)) {
            $cloudinaryImageService->deleteImage($gallery->cloudinary_public_id);
        } else {
            $this->deleteLegacyLocalImage($gallery->image_path, $gallery->storage_disk);
        }

        $gallery->delete();

        $auditLog->log(
            $request->user(),
            'gallery.deleted',
            $gallery,
            "Foto galeri {$galleryTitle} dihapus.",
            ['title' => $galleryTitle]
        );

        return back()->with('success', 'Foto galeri berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function auditFields(Gallery $gallery): array
    {
        return $gallery->only([
            'title',
            'description',
            'image_path',
            'cloudinary_public_id',
            'cloudinary_secure_url',
            'storage_disk',
            'is_active',
        ]);
    }

    private function deleteLegacyLocalImage(?string $imagePath, ?string $storageDisk): void
    {
        if (blank($imagePath) || $storageDisk === 'cloudinary') {
            return;
        }

        if (Str::startsWith($imagePath, ['http://', 'https://', 'assets/', '/assets/', 'storage/', '/storage/'])) {
            return;
        }

        Storage::disk($storageDisk ?: 'public')->delete($imagePath);
    }
}
