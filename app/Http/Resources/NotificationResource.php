<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the notification into the locked Batch 6 JSON contract.
     *
     * Hanya field notifikasi yang aman & milik tenant terautentikasi. Payload `data`
     * (yang dapat berisi metadata internal booking/payment/unique_key) **tidak** diekspos
     * untuk menghindari pembocoran data internal.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at?->toISO8601String(),
            'updated_at' => $this->updated_at?->toISO8601String(),
        ];
    }
}
