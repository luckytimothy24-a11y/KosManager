<?php

namespace Database\Factories;

use App\Models\MarketplaceReview;
use Illuminate\Database\Eloquence\Factories\Factory;

/**
 * @extends Factory<MarketplaceReview>
 */
class MarketplaceReviewFactory extends Factory
{
    protected $faker;

    public function definition(): array
    {
        return [
            'transaction_id' => null,
            'reviewer_id' => null,
            'reviewee_id' => null,
            'rating' => random_int(1, 5),
            'comment' => null,
        ];
    }
}
