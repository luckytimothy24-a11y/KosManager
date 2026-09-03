<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagihanResource extends JsonResource
{
    /**
     * Transform the tagihan into the locked Batch 4 JSON contract.
     *
     * Hanya field publik + data milik tenant terautentikasi. Tidak mengekspos
     * penghuni lain, data internal, payment, atau field sensitif.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bill_number' => $this->bill_number,
            'bill_type' => $this->bill_type,
            'kos_id' => $this->kamar?->kos_id,
            'kamar_id' => $this->kamar_id,
            'period_start' => $this->period_start?->toDateString(),
            'period_end' => $this->period_end?->toDateString(),
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'penalty' => (float) $this->penalty,
            'total' => (float) $this->total,
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status,
            'pembayaran' => PembayaranResource::collection($this->whenLoaded('pembayarans')),
        ];
    }
}
