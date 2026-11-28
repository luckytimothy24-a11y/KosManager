<?php

namespace Database\Factories;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'kos_id' => Kos::factory(),
            'kamar_id' => Kamar::factory(),
            'category' => 'kerusakan',
            'description' => 'Deskripsi work order test.',
            'priority' => 'normal',
            'status' => WorkOrder::STATUS_PENDING,
        ];
    }
}
