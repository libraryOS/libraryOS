<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\PatronType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PatronType>
 */
class PatronTypeFactory extends Factory
{
    protected $model = PatronType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'key' => Str::slug(fake()->unique()->words(2, true)),
            'name' => fake()->words(2, true),
            'description' => null,
            'is_active' => true,
            'membership_duration_days' => null,
            'max_loans' => null,
            'keep_loan_history' => false,
            'can_receive_notifications' => true,
            'minimum_age' => null,
            'maximum_age' => null,
        ];
    }
}
