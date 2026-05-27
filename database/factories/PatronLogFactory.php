<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Patron;
use App\Models\PatronLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatronLog>
 */
class PatronLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $organization = Organization::factory();
        $patron = Patron::factory()->for($organization);

        return [
            'organization_id' => $organization,
            'patron_id' => $patron,
            'actor_type' => User::class,
            'actor_id' => User::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'status_changed', 'note_added']),
            'description' => fake()->optional()->sentence(),
            'metadata' => fake()->optional()->randomElement([
                ['source' => 'system', 'ip' => fake()->ipv4()],
                ['source' => 'staff', 'changes' => ['status' => ['from' => 'active', 'to' => 'suspended']]],
            ]),
        ];
    }

    public function withPatronActor(): self
    {
        return $this->state(fn (): array => [
            'actor_type' => Patron::class,
            'actor_id' => Patron::factory(),
        ]);
    }

    public function withoutActor(): self
    {
        return $this->state(fn (): array => [
            'actor_type' => null,
            'actor_id' => null,
        ]);
    }
}
