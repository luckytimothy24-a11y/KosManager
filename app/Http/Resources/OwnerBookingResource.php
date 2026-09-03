<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerBookingResource extends JsonResource
{
    /**
     * Transform the booking into the locked owner JSON contract (Batch 8).
     *
     * Owner-oriented: includes kos, kamar and the tenant who booked (id + name
     * only — no sensitive tenant PII like email/phone/address). Does not expose
     * payment proof path, internal metadata, or authorization data.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_code' => $this->booking_code,
            'tenant' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'kos' => [
                'id' => $this->kos->id,
                'name' => $this->kos->name,
            ],
            'kamar' => [
                'id' => $this->kamar->id,
                'name' => $this->kamar->room_name,
                'room_number' => $this->kamar->room_number,
            ],
            'booking_date' => $this->booking_date?->toDateString(),
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'rental_type' => $this->rental_type,
            'price' => $this->price,
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
