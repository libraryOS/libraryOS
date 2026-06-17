<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Work> */
class WorkFactory extends Factory
{
    protected $model = Work::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'title' => fake()->sentence(3),
            'subtitle' => fake()->optional()->sentence(3),
            'description' => fake()->optional()->paragraph(),
        ];
    }
}
