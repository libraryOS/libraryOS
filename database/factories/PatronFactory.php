<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\Patron;
use App\Models\PatronType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Patron> */
class PatronFactory extends Factory
{
    protected $model = Patron::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => null,
            'patron_type_id' => PatronType::factory(),
            'home_branch_id' => Branch::factory(),
            'card_number' => strtoupper((string) fake()->bothify('PATRON-######')),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => 'active',
            'membership_expires_at' => fake()->optional()->dateTimeBetween('now', '+2 years'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function withUser(): self
    {
        return $this->state(fn (): array => [
            'user_id' => User::factory(),
        ]);
    }
}
