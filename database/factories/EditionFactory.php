<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Edition;
use App\Models\ItemType;
use App\Models\Organization;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Edition> */
class EditionFactory extends Factory
{
    protected $model = Edition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'work_id' => fn (array $attributes): int => Work::factory()->create([
                'organization_id' => $attributes['organization_id'],
            ])->id,
            'item_type_id' => fn (array $attributes): int => ItemType::factory()->create([
                'organization_id' => $attributes['organization_id'],
            ])->id,
            'title' => fake()->sentence(3),
            'isbn' => fake()->optional()->isbn13(),
            'publisher' => fake()->optional()->company(),
            'publication_year' => fake()->optional()->numberBetween(1450, (int) date('Y')),
            'language' => fake()->optional()->languageCode(),
            'cover_image_path' => fake()->optional()->filePath(),
        ];
    }
}
