<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KontrakResource extends JsonResource
{
    /**
     * Transform the kontrak into the locked Batch 5 JSON contract.
     *
     * Hanya field publik + data milik tenant terautentikasi. Tidak mengekspos
     * penghuni lain, owner credential, data kontrak tenant lain, atau field internal.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contract_number' => $this->contract_number,
            'kos' => [
                'id' => $this->kos?->id,
                'name' => $this->kos?->name,
                'address' => $this->kos?->address,
                'city' => null,
                'photo' => $this->kos?->photo,
            ],
            'kamar' => [
                'id' => $this->kamar?->id,
                'name' => $this->kamar?->room_name,
                'type' => $this->kamar?->room_type,
                'photo' => $this->kamar?->photo,
            ],
            'rental_type' => $this->rental_type,
            'rental_price' => (float) $this->rental_price,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
