<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'cloudinary_public_id',
        'cloudinary_secure_url',
        'storage_disk',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getImageUrlAttribute(): string
    {
        if (filled($this->cloudinary_secure_url)) {
            return $this->cloudinary_secure_url;
        }

        if (blank($this->image_path)) {
            return asset('assets/images/gallery-1.jpg');
        }

        if (Str::startsWith($this->image_path, ['http://', 'https://'])) {
            return $this->image_path;
        }

        if (Str::startsWith($this->image_path, ['assets/', '/assets/', 'storage/', '/storage/'])) {
            return asset(ltrim($this->image_path, '/'));
        }

        return Storage::disk($this->storage_disk ?: 'public')->url($this->image_path);
    }
}
