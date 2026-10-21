<?php

namespace App\Http\Requests;

use App\Rules\SafeImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->isSuperAdmin() || $user->isOwner() || $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'kos_id' => ['required', 'exists:kos,id'],
            'room_number' => ['required', 'string', 'max:50'],
            'room_name' => ['required', 'string', 'max:255'],
            'floor' => ['nullable', 'integer', 'min:0'],
            'room_type' => ['required', 'string', 'max:100'],
            // 1 kamar = 1 penghuni — patungan/multi dihapus
            'capacity' => ['nullable', 'integer', 'in:1'],
            'billing_mode' => ['nullable', 'string', 'in:personal'],
            'daily_price' => ['required', 'numeric', 'min:0'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'weekly_price' => ['nullable', 'numeric', 'min:0'],
            'area' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', new SafeImage],
            'status' => ['sometimes', 'in:available,booked,occupied,maintenance'],
            'fasilitas' => ['nullable', 'array'],
            'fasilitas.*' => [
                'exists:fasilitas,id',
                Rule::exists('fasilitas', 'id')->where('type', 'kamar')->where('is_active', true),
            ],
        ];
    }

    protected function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $capacity = $this->input('capacity');
            $billingMode = $this->input('billing_mode');

            if ($capacity !== null && (int) $capacity > 1) {
                $validator->errors()->add('capacity', 'Satu kamar hanya untuk satu penghuni. Patungan tidak lagi didukung.');
            }
            if ($billingMode !== null && $billingMode !== 'personal') {
                $validator->errors()->add('billing_mode', 'Patungan tidak lagi didukung. Satu kamar untuk satu penghuni.');
            }
        });
    }
}
