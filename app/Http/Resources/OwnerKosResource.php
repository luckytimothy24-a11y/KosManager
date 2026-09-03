<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerKosResource extends JsonResource
{
    /**
     * Transform the kos into the locked owner JSON contract (Batch 7).
     *
     * Owner-orinted: exposes the operational fields a property owner needs
     * (status, phone, location, general facilities, rules, payment info and
     * aggregate counts). Does NOT leak tenant-facing marketing-only fields.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'description' => $this->description,
            'phone' => $this->phone,
            'photo' => $this->photo,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'general_facilities' => $this->general_facilities,
            'rules' => $this->rules,
            'payment_info' => $this->payment_info,
            'status' => $this->status,
            'facilities' => $this->whenLoaded('fasilitas', fn () => $this->fasilitas->pluck('name')->values()->all(), []),
            'kamar_count' => (int) ($this->kamar_count ?? 0),
            'penghuni_count' => (int) ($this->penghuni_count ?? 0),
            'bookings_count' => (int) ($this->bookings_count ?? 0),
        ];
    }
}
