<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $imageRule = $this->isMethod('post') ? 'required' : 'nullable';

        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'image' => [$imageRule, 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
