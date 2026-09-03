<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KamarResource extends JsonResource
{
    /**
     * Transform the kamar into the locked JSON contract.
     *
     * Adaptasi field ke domain existing:
     * - name: 'room_name' di DB
     * - type: 'room_type' di DB
     * - price: 'monthly_price' di DB
     * - status: 'status' enum existing
     * - is_available: derived dari status === 'available'
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->room_name,
            'type' => $this->room_type,
            'price' => $this->monthly_price,
            'photo' => $this->photo,
            'status' => $this->status,
            'is_available' => $this->status === 'available',
        ];
    }
}
