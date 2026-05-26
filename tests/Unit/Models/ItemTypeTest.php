<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\ItemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ItemTypeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_an_organization(): void
    {
        $itemType = ItemType::factory()->create();

        $this->assertTrue($itemType->organization()->exists());
    }

    #[Test]
    public function it_returns_name_when_set(): void
    {
        $itemType = ItemType::factory()->make([
            'name' => 'Book',
            'name_translation_key' => null,
        ]);

        $this->assertEquals('Book', $itemType->getName());
    }

    #[Test]
    public function it_returns_translated_name_when_name_is_null(): void
    {
        $itemType = ItemType::factory()->make([
            'name' => null,
            'name_translation_key' => 'item_types.dvd',
        ]);

        $this->assertEquals('item_types.dvd', $itemType->getName());
    }
}
