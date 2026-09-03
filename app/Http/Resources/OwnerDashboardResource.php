<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerDashboardResource extends JsonResource
{
    /**
     * Transform the owner dashboard statistics array into the locked JSON contract.
     *
     * Only re-exposes the statistics that the web owner dashboard already shows
     * (see DashboardController::owner()) — no new business metric is invented.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'kos' => [
                'total' => (int) ($this->resource['total_kos'] ?? 0),
            ],
            'kamar' => [
                'total' => (int) ($this->resource['total_kamar'] ?? 0),
                'available' => (int) ($this->resource['total_kamar_available'] ?? 0),
                'occupied' => (int) ($this->resource['total_kamar_occupied'] ?? 0),
                'maintenance' => (int) ($this->resource['total_kamar_maintenance'] ?? 0),
            ],
            'penghuni' => [
                'active' => (int) ($this->resource['total_penghunis'] ?? 0),
            ],
            'booking' => [
                'needs_checkin' => (int) ($this->resource['needs_checkin'] ?? 0),
            ],
            'billing' => [
                'pending_payments' => (int) ($this->resource['pending_payments'] ?? 0),
                'tagihan_outstanding' => (int) ($this->resource['tagihan_outstanding'] ?? 0),
                'tagihan_overdue' => (int) ($this->resource['tagihan_overdue'] ?? 0),
            ],
            'finance' => [
                'total_revenue' => (float) ($this->resource['total_revenue'] ?? 0),
            ],
        ];
    }
}
