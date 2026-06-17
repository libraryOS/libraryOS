<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Edition;
use App\Models\ItemType;
use App\Models\Organization;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EditionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_an_organization(): void
    {
        $organization = Organization::factory()->create();
        $edition = Edition::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $this->assertTrue($edition->organization()->exists());
        $this->assertEquals($organization->id, $edition->organization?->id);
    }

    #[Test]
    public function it_belongs_to_a_work(): void
    {
        $organization = Organization::factory()->create();
        $work = Work::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $edition = Edition::factory()->create([
            'organization_id' => $organization->id,
            'work_id' => $work->id,
        ]);

        $this->assertTrue($edition->work()->exists());
        $this->assertEquals($work->id, $edition->work?->id);
    }

    #[Test]
    public function it_belongs_to_an_item_type(): void
    {
        $organization = Organization::factory()->create();
        $itemType = ItemType::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $edition = Edition::factory()->create([
            'organization_id' => $organization->id,
            'item_type_id' => $itemType->id,
        ]);

        $this->assertTrue($edition->itemType()->exists());
        $this->assertEquals($itemType->id, $edition->itemType?->id);
    }
}
