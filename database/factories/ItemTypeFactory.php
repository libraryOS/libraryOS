<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ItemType;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ItemType>
 */
class ItemTypeFactory extends Factory
{
    protected $model = ItemType::class;

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
            'name_translation_key' => null,
            'description' => null,
            'is_loanable' => true,
            'is_holdable' => true,
            'is_visible_in_catalog' => true,
            'default_loan_days' => null,
        ];
    }
}
