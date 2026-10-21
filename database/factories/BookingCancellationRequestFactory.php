<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingCancellationRequest>
 */
class BookingCancellationRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'requested_by' => User::factory(),
            'status' => BookingCancellationRequest::STATUS_PENDING,
            'reason' => fake()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => BookingCancellationRequest::STATUS_PENDING]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => BookingCancellationRequest::STATUS_APPROVED,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => BookingCancellationRequest::STATUS_REJECTED,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
            'rejection_reason' => 'Ditolak pengelola.',
        ]);
    }
}
