<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'user';
    }

    public function rules(): array
    {
        return [
            'package_id' => ['required', 'exists:services,id'],
            'visit_date' => ['required', 'date', 'after_or_equal:today'],
            'participant_count' => ['required', 'integer', 'min:1', 'max:100'],
            'contact_name' => ['required', 'string', 'max:100'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'contact_email' => ['required', 'email', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
