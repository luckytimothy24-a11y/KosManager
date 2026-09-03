<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /**
     * Transform the favorite into the locked JSON contract.
     *
     * Nested 'kos' hanya menggunakan public marketplace fields.
     * Tidak mengekspos owner, tenant, payment, atau internal fields.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kos' => [
                'id' => $this->kos->id,
                'name' => $this->kos->name,
                'slug' => null,
                'address' => $this->kos->address,
                'city' => null,
                'photo' => $this->kos->photo,
                'price_min' => $this->kos->price_min ?? null,
                'price_max' => $this->kos->price_max ?? null,
                'is_available' => ($this->kos->status === 'active')
                    && (($this->kos->kamar_tersedia ?? 0) > 0),
            ],
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
