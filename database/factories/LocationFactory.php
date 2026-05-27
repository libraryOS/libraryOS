<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Location> */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'branch_id' => Branch::factory(),
            'parent_id' => null,
            'name' => fake()->words(2, true),
            'code' => null,
            'description' => null,
            'is_active' => true,
            'is_public' => true,
            'supports_pickups' => false,
            'supports_returns' => false,
        ];
    }
}
