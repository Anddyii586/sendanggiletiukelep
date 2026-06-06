<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.site-settings.edit', [
            'settings' => SiteSetting::asKeyValue(),
            'fields' => $this->fields(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            collect($this->fields())
                ->mapWithKeys(fn (string $label, string $key) => [$key => ['nullable', 'string', 'max:2000']])
                ->all()
        );

        foreach ($validated as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Site settings berhasil diperbarui.');
    }

    private function fields(): array
    {
        return [
            'destination_name' => 'Nama destinasi',
            'short_description' => 'Deskripsi singkat',
            'destination_description' => 'Deskripsi destinasi',
            'address' => 'Alamat',
            'opening_hours' => 'Jam operasional',
            'facilities' => 'Fasilitas',
            'contact_phone' => 'Nomor kontak',
            'contact_email' => 'Email kontak',
            'google_maps_url' => 'Google Maps URL',
            'google_maps_embed_url' => 'Google Maps Embed URL',
        ];
    }
}
