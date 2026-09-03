<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerKamarResource extends JsonResource
{
    /**
     * Transform the kamar into the locked owner JSON contract (Batch 7).
     *
     * Exposes the operational room fields a property owner manages.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kos_id' => $this->kos_id,
            'room_number' => $this->room_number,
            'room_name' => $this->room_name,
            'floor' => $this->floor,
            'room_type' => $this->room_type,
            'daily_price' => $this->daily_price,
            'monthly_price' => $this->monthly_price,
            'area' => $this->area,
            'description' => $this->description,
            'photo' => $this->photo,
            'status' => $this->status,
        ];
    }
}
