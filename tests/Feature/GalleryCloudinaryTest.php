<?php

use App\Models\Gallery;
use App\Models\User;
use App\Services\CloudinaryImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function phase6User(string $role = 'user', array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => $role,
        'phone' => '081234567890',
    ], $attributes));
}

test('admin bisa membuat gallery dengan mocked cloudinary upload', function (): void {
    $admin = phase6User('admin');
    $secureUrl = 'https://res.cloudinary.com/demo/image/upload/v1/sendang-gile/galleries/photo.jpg';
    $publicId = 'sendang-gile/galleries/photo';

    $cloudinary = Mockery::mock(CloudinaryImageService::class);
    $cloudinary
        ->shouldReceive('uploadGalleryImage')
        ->once()
        ->with(Mockery::type(UploadedFile::class))
        ->andReturn([
            'secure_url' => $secureUrl,
            'public_id' => $publicId,
        ]);
    $cloudinary->shouldNotReceive('deleteImage');
    $this->app->instance(CloudinaryImageService::class, $cloudinary);

    $this->actingAs($admin)
        ->post(route('admin.galleries.store'), [
            'title' => 'Air Terjun Cloudinary',
            'description' => 'Foto test Cloudinary',
            'image' => UploadedFile::fake()->image('gallery.jpg')->size(512),
            'is_active' => '1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.galleries.index'));

    $gallery = Gallery::first();

    expect($gallery)->not->toBeNull()
        ->and($gallery->cloudinary_secure_url)->toBe($secureUrl)
        ->and($gallery->cloudinary_public_id)->toBe($publicId)
        ->and($gallery->storage_disk)->toBe('cloudinary')
        ->and($gallery->image_path)->toBe($secureUrl);
});

test('public gallery menggunakan image_url cloudinary', function (): void {
    $secureUrl = 'https://res.cloudinary.com/demo/image/upload/v1/sendang-gile/galleries/public.jpg';

    Gallery::create([
        'title' => 'Public Cloudinary',
        'description' => 'Visible gallery',
        'image_path' => 'legacy/path.jpg',
        'cloudinary_secure_url' => $secureUrl,
        'cloudinary_public_id' => 'sendang-gile/galleries/public',
        'storage_disk' => 'cloudinary',
        'is_active' => true,
    ]);

    $this->get(route('gallery'))
        ->assertOk()
        ->assertSee($secureUrl, false);
});

test('update gallery dengan gambar baru menghapus public id lama', function (): void {
    $admin = phase6User('admin');
    $gallery = Gallery::create([
        'title' => 'Foto Lama',
        'description' => 'Sebelum update',
        'image_path' => 'https://res.cloudinary.com/demo/image/upload/old.jpg',
        'cloudinary_secure_url' => 'https://res.cloudinary.com/demo/image/upload/old.jpg',
        'cloudinary_public_id' => 'sendang-gile/galleries/old',
        'storage_disk' => 'cloudinary',
        'is_active' => true,
    ]);

    $cloudinary = Mockery::mock(CloudinaryImageService::class);
    $cloudinary
        ->shouldReceive('uploadGalleryImage')
        ->once()
        ->with(Mockery::type(UploadedFile::class))
        ->andReturn([
            'secure_url' => 'https://res.cloudinary.com/demo/image/upload/new.jpg',
            'public_id' => 'sendang-gile/galleries/new',
        ]);
    $cloudinary
        ->shouldReceive('deleteImage')
        ->once()
        ->with('sendang-gile/galleries/old')
        ->andReturnTrue();
    $this->app->instance(CloudinaryImageService::class, $cloudinary);

    $this->actingAs($admin)
        ->put(route('admin.galleries.update', $gallery), [
            'title' => 'Foto Baru',
            'description' => 'Sesudah update',
            'image' => UploadedFile::fake()->image('gallery-new.jpg')->size(512),
            'is_active' => '1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.galleries.index'));

    $gallery->refresh();

    expect($gallery->cloudinary_secure_url)->toBe('https://res.cloudinary.com/demo/image/upload/new.jpg')
        ->and($gallery->cloudinary_public_id)->toBe('sendang-gile/galleries/new')
        ->and($gallery->storage_disk)->toBe('cloudinary');
});

test('delete gallery menghapus cloudinary image jika public id ada', function (): void {
    $admin = phase6User('admin');
    $gallery = Gallery::create([
        'title' => 'Delete Cloudinary',
        'description' => 'Hapus image Cloudinary',
        'image_path' => 'https://res.cloudinary.com/demo/image/upload/delete.jpg',
        'cloudinary_secure_url' => 'https://res.cloudinary.com/demo/image/upload/delete.jpg',
        'cloudinary_public_id' => 'sendang-gile/galleries/delete',
        'storage_disk' => 'cloudinary',
        'is_active' => true,
    ]);

    $cloudinary = Mockery::mock(CloudinaryImageService::class);
    $cloudinary
        ->shouldReceive('deleteImage')
        ->once()
        ->with('sendang-gile/galleries/delete')
        ->andReturnTrue();
    $cloudinary->shouldNotReceive('uploadGalleryImage');
    $this->app->instance(CloudinaryImageService::class, $cloudinary);

    $this->actingAs($admin)
        ->delete(route('admin.galleries.destroy', $gallery))
        ->assertSessionHasNoErrors();

    expect(Gallery::whereKey($gallery->id)->exists())->toBeFalse();
});

test('fallback image_url bekerja untuk image_path local lama', function (): void {
    $gallery = Gallery::create([
        'title' => 'Legacy Local',
        'description' => 'Path local lama',
        'image_path' => 'galleries/legacy.jpg',
        'is_active' => true,
    ]);

    expect($gallery->image_url)->toBe(Storage::disk('public')->url('galleries/legacy.jpg'));
});

test('user biasa tidak bisa akses admin gallery create dan store', function (): void {
    $user = phase6User('user');

    $this->actingAs($user)
        ->get(route('admin.galleries.create'))
        ->assertRedirect(route('my-bookings.index'));

    $this->actingAs($user)
        ->post(route('admin.galleries.store'), [
            'title' => 'Unauthorized',
            'description' => 'Tidak boleh',
            'image' => UploadedFile::fake()->image('unauthorized.jpg')->size(128),
            'is_active' => '1',
        ])
        ->assertRedirect(route('my-bookings.index'));
});
