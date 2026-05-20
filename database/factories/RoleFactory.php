<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'key' => fake()->unique()->slug(2),
            'name' => fake()->randomElement([
                'Regional Manager',
                'Assistant to the Regional Manager',
                'Sales Representative',
                'Accountant',
                'Receptionist',
                'HR Representative',
                'Warehouse Supervisor',
            ]),
            'description' => fake()->optional()->sentence(),
            'is_system' => false,
        ];
    }

    /**
     * Indicate that the role is a system role (not tied to an organization).
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => null,
            'is_system' => true,
        ]);
    }
}
