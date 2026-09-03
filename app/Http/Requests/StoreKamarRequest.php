<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin', 'owner', 'admin');
    }

    public function rules(): array
    {
        return [
            'kos_id' => ['required', 'exists:kos,id'],
            'room_number' => ['required', 'string', 'max:50'],
            'room_name' => ['required', 'string', 'max:255'],
            'floor' => ['nullable', 'integer', 'min:0'],
            'room_type' => ['required', 'string', 'max:100'],
            'daily_price' => ['required', 'numeric', 'min:0'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'area' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['sometimes', 'in:available,booked,occupied,maintenance'],
            'fasilitas' => ['nullable', 'array'],
            'fasilitas.*' => [
                'exists:fasilitas,id',
                Rule::exists('fasilitas', 'id')->where('type', 'kamar')->where('is_active', true),
            ],
        ];
    }
}
