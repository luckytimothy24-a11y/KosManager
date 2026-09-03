<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerKontrakResource extends JsonResource
{
    /**
     * Transform the kontrak into the locked owner JSON contract (Batch 9).
     *
     * Owner-oriented: includes the penghuni (tenant) who holds the contract,
     * its kos and kamar, and contract terms. Exposes minimum tenant identity
     * (id + name) only — no sensitive tenant PII. No payment proof/internal
     * metadata/authorization data.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contract_number' => $this->contract_number,
            'penghuni' => [
                'id' => $this->penghuni?->user_id,
                'name' => $this->penghuni?->user?->name,
            ],
            'kos' => [
                'id' => $this->kos?->id,
                'name' => $this->kos?->name,
            ],
            'kamar' => [
                'id' => $this->kamar?->id,
                'name' => $this->kamar?->room_name,
                'room_number' => $this->kamar?->room_number,
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
