<?php

namespace App\Http\Requests;

use App\Rules\SafeImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin', 'owner', 'admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['nullable', 'numeric', 'min:-180', 'max:180'],
            'description' => ['nullable', 'string'],
            'phone' => ['required', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', new SafeImage],
            'general_facilities' => ['nullable', 'string'],
            'rules' => ['nullable', 'string'],
            'payment_info' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', 'in:active,inactive'],
            'fasilitas' => ['nullable', 'array'],
            'fasilitas.*' => [
                'exists:fasilitas,id',
                Rule::exists('fasilitas', 'id')->where('type', 'kos')->where('is_active', true),
            ],
            'admins' => ['nullable', 'array'],
            'admins.*' => [
                'exists:users,id',
                Rule::exists('users', 'id')->where('role', 'admin'),
            ],
        ];
    }
}
