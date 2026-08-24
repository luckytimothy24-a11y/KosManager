<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFasilitasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        $fasilitasId = $this->route('fasilitas')?->id;

        return [
            'name' => ['required', 'string', 'max:255', 'unique:fasilitas,name'.($fasilitasId ? ','.$fasilitasId : '')],
            'icon' => ['nullable', 'string', 'max:255'],
        ];
    }
}
