<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KosDetailResource extends JsonResource
{
    /**
     * Transform the kos detail into the locked JSON contract.
     *
     * Adaptasi field ke domain existing:
     * - slug: tidak ada di DB → null
     * - city: tidak ada di DB → null
     * - photos: DB punya single 'photo' → wrap jadi array
     * - facilities: dari relasi fasilitas → array of strings
     * - available_rooms & is_available: dari availability logic existing
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => null,
            'address' => $this->address,
            'city' => null,
            'description' => $this->description,
            'price_min' => $this->price_min ?? null,
            'price_max' => $this->price_max ?? null,
            'photos' => $this->photo ? [$this->photo] : [],
            'facilities' => $this->whenLoaded('fasilitas', fn () => $this->fasilitas->pluck('name')->values()->all(), []),
            'available_rooms' => $this->available_rooms ?? 0,
            'is_available' => ($this->status === 'active') && ($this->available_rooms ?? 0) > 0,
        ];
    }
}
