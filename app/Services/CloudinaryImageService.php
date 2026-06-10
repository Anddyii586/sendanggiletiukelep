<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class CloudinaryImageService
{
    /**
     * @return array{secure_url: string, public_id: string}
     */
    public function uploadGalleryImage(UploadedFile $file): array
    {
        try {
            $response = $this->cloudinary()->uploadApi()->upload($file->getRealPath(), [
                'folder' => config('cloudinary.gallery_folder', 'sendang-gile/galleries'),
                'resource_type' => 'image',
                'allowed_formats' => ['jpg', 'jpeg', 'png', 'webp'],
                'use_filename' => true,
                'unique_filename' => true,
                'overwrite' => false,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Cloudinary gallery upload failed.', [
                'exception' => $exception::class,
            ]);

            throw new RuntimeException('Upload gambar ke Cloudinary gagal. Coba lagi atau cek konfigurasi Cloudinary.');
        }

        $secureUrl = $response['secure_url'] ?? null;
        $publicId = $response['public_id'] ?? null;

        if (! is_string($secureUrl) || $secureUrl === '' || ! is_string($publicId) || $publicId === '') {
            Log::warning('Cloudinary gallery upload returned an incomplete response.');

            throw new RuntimeException('Upload gambar ke Cloudinary gagal. Respons Cloudinary tidak lengkap.');
        }

        return [
            'secure_url' => $secureUrl,
            'public_id' => $publicId,
        ];
    }

    public function deleteImage(?string $publicId): bool
    {
        if (blank($publicId)) {
            return true;
        }

        try {
            $response = $this->cloudinary()->uploadApi()->destroy($publicId, [
                'resource_type' => 'image',
                'invalidate' => true,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Cloudinary image delete failed.', [
                'exception' => $exception::class,
                'public_id' => $publicId,
            ]);

            return false;
        }

        return in_array($response['result'] ?? null, ['ok', 'not found'], true);
    }

    protected function cloudinary(): Cloudinary
    {
        $cloudinaryUrl = config('cloudinary.cloudinary_url');

        if (filled($cloudinaryUrl)) {
            return new Cloudinary($cloudinaryUrl);
        }

        return new Cloudinary([
            'cloud' => array_filter(config('cloudinary.cloud', [])),
        ]);
    }
}
