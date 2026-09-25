<?php

namespace Database\Factories;

use App\Models\DamageReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class DamageReportFactory extends Factory
{
    protected $model = DamageReport::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'kos_id' => 1,
            'kamar_id' => 1,
            'category' => 'general',
            'description' => 'Damage report description',
            'photos' => null,
            'status' => 'pending',
            'owner_reply' => null,
            'rejection_reason' => null,
            'owner_id' => null,
            'resolved_at' => null,
        ];
    }
}
