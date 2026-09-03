<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerTagihanResource extends JsonResource
{
    /**
     * Transform the tagihan into the locked owner JSON contract (Batch 9).
     *
     * Owner-oriented: bill of an authorized kontrak belonging to the owner's
     * property. Includes the penghuni (id + name only), the parent kontrak id,
     * period, amounts (decimal cast to float) and status. No payment proof /
     * internal payment payload / sensitive data.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bill_number' => $this->bill_number,
            'kontrak_id' => $this->kontrak_id,
            'penghuni' => [
                'id' => $this->penghuni?->user_id,
                'name' => $this->penghuni?->user?->name,
            ],
            'bill_type' => $this->bill_type,
            'kamar_id' => $this->kamar_id,
            'period_start' => $this->period_start?->toDateString(),
            'period_end' => $this->period_end?->toDateString(),
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'penalty' => (float) $this->penalty,
            'total' => (float) $this->total,
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status,
        ];
    }
}
