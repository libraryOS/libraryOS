<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_an_organization(): void
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        $this->assertTrue($location->organization()->exists());
    }

    #[Test]
    public function it_belongs_to_a_branch(): void
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        $this->assertTrue($location->branch()->exists());
    }

    #[Test]
    public function it_can_belong_to_a_parent_location(): void
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $parent = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);
        $child = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'parent_id' => $parent->id,
        ]);

        $this->assertTrue($child->parent()->exists());
        $this->assertEquals($parent->id, $child->parent->id);
    }

    #[Test]
    public function it_has_many_children(): void
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $parent = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);
        Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'parent_id' => $parent->id,
        ]);

        $this->assertEquals(1, $parent->children()->count());
    }

    #[Test]
    public function it_has_no_parent_by_default(): void
    {
        $location = Location::factory()->create();

        $this->assertNull($location->parent_id);
        $this->assertFalse($location->parent()->exists());
    }
}
