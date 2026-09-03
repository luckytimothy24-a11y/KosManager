<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PembayaranResource extends JsonResource
{
    /**
     * Transform the pembayaran into the locked Batch 4 JSON contract.
     *
     * Tidak mengekspos internal filesystem path (proof_file), data tenant lain,
     * verifier, atau field internal lain.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'tagihan_id' => $this->tagihan_id,
            'amount' => (float) $this->amount,
            'payment_date' => $this->payment_date?->toDateString(),
            'payment_method' => $this->payment_method,
            'verification_status' => $this->verification_status,
            'paid_at' => $this->verified_at?->toDateTimeString(),
            'admin_notes' => $this->admin_notes,
            'gateway_provider' => $this->gateway_provider,
            'proof_available' => (bool) $this->proof_file,
        ];
    }
}
