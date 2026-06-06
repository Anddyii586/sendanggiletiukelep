<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if ($this->shouldSeedDefaultUsers()) {
            User::updateOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin Pengelola',
                    'phone' => '081234567890',
                    'password' => Hash::make('@&min586'),
                    'role' => 'admin',
                ]
            );

            User::updateOrCreate(
                ['email' => 'user@example.com'],
                [
                    'name' => 'Sample User',
                    'phone' => '081111111111',
                    'password' => Hash::make('123123123'),
                    'role' => 'user',
                ]
            );
        }

        $services = [
            [
                'name' => 'Tiket Masuk Wisata',
                'description' => 'Akses kunjungan ke kawasan Sendang Gile dan Tiu Kelep untuk satu orang.',
                'price' => 10000,
                'pricing_type' => 'per_person',
                'image_path' => 'assets/images/gallery-1.jpg',
                'is_featured' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Tiket Wisata + Guide',
                'description' => 'Paket kunjungan dengan pendampingan guide lokal untuk jalur trekking dan informasi destinasi.',
                'price' => 150000,
                'pricing_type' => 'per_trip',
                'image_path' => 'assets/images/gallery-2.jpg',
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Paket Rombongan',
                'description' => 'Paket kunjungan rombongan dengan koordinasi guide lokal dan alur kedatangan lebih rapi.',
                'price' => 250000,
                'pricing_type' => 'per_trip',
                'image_path' => 'assets/images/gallery-3.jpg',
                'is_featured' => false,
                'sort_order' => 3,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                [...$service, 'slug' => Str::slug($service['name']), 'is_active' => true]
            );
        }

        Service::whereIn('name', ['Guide Lokal', 'Paket Tiket dan Guide'])
            ->update(['is_active' => false]);

        $galleries = [
            [
                'title' => 'Air Terjun Sendang Gile',
                'image_path' => 'assets/images/gallery-1.jpg',
                'description' => 'Pemandangan air terjun dengan suasana hutan tropis Senaru.',
            ],
            [
                'title' => 'Jalur Trekking Tiu Kelep',
                'image_path' => 'assets/images/gallery-2.jpg',
                'description' => 'Jalur alami menuju kawasan air terjun Tiu Kelep.',
            ],
            [
                'title' => 'Alam Lombok Utara',
                'image_path' => 'assets/images/gallery-3.jpg',
                'description' => 'Lanskap hijau yang menjadi daya tarik utama kawasan wisata.',
            ],
        ];

        foreach ($galleries as $gallery) {
            Gallery::updateOrCreate(
                ['title' => $gallery['title']],
                [...$gallery, 'is_active' => true]
            );
        }

        $settings = [
            'destination_name' => 'Sendang Gile & Tiu Kelep Waterfalls',
            'short_description' => 'Website informasi dan booking wisata air terjun Sendang Gile dan Tiu Kelep di Senaru.',
            'destination_description' => 'Sendang Gile dan Tiu Kelep adalah destinasi air terjun populer di kaki Gunung Rinjani. Website ini membantu wisatawan melihat informasi layanan, galeri, lokasi, checkout online, e-ticket, dan review resmi.',
            'address' => 'Senaru, Bayan, Lombok Utara, Nusa Tenggara Barat',
            'opening_hours' => '08.00 - 17.00 WITA',
            'facilities' => 'Area parkir, guide lokal, jalur trekking, toilet umum, spot foto, warung lokal.',
            'contact_phone' => '0812-3456-7890',
            'contact_email' => 'info@sendanggile-tiukelep.test',
            'google_maps_url' => 'https://maps.google.com/?q=Sendang+Gile+Waterfall',
            'google_maps_embed_url' => 'https://www.google.com/maps?q=Sendang%20Gile%20Waterfall&output=embed',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }

    private function shouldSeedDefaultUsers(): bool
    {
        return app()->environment(['local', 'testing']);
    }
}
