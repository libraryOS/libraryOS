<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'country_id' => Country::factory(),
            'name' => fake()->company(),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => null,
            'city' => fake()->city(),
            'state_province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'timezone' => fake()->timezone(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Branch $branch): void {
            $branch->slug = $branch->id . '-' . Str::lower($branch->name);
            $branch->save();
        });
    }
}
