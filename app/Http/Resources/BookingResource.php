<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the booking into the locked JSON contract.
     *
     * Hanya public marketplace fields + data milik user terautentikasi.
     * Tidak mengekspos owner credential, data tenant lain, payment,
     * atribut penting booking lain, atau field internal.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_code' => $this->booking_code,
            'kos' => [
                'id' => $this->kos->id,
                'name' => $this->kos->name,
                'slug' => null,
                'address' => $this->kos->address,
                'city' => null,
                'photo' => $this->kos->photo,
            ],
            'kamar' => [
                'id' => $this->kamar->id,
                'name' => $this->kamar->room_name,
                'type' => $this->kamar->room_type,
                'photo' => $this->kamar->photo,
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
