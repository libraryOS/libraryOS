<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\ItemType;
use App\Models\Member;
use App\Models\Organization;
use App\Models\PatronType;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_many_memberships(): void
    {
        $organization = Organization::factory()->create();
        Member::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $this->assertTrue($organization->members()->exists());
    }

    #[Test]
    public function it_has_many_branches(): void
    {
        $organization = Organization::factory()->create();
        Branch::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $this->assertTrue($organization->branches()->exists());
    }

    #[Test]
    public function it_has_many_item_types(): void
    {
        $organization = Organization::factory()->create();
        ItemType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $this->assertTrue($organization->itemTypes()->exists());
    }

    #[Test]
    public function it_has_many_patron_types(): void
    {
        $organization = Organization::factory()->create();
        PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $this->assertTrue($organization->patronTypes()->exists());
    }

    #[Test]
    public function it_has_many_permissions(): void
    {
        $organization = Organization::factory()->create();
        Permission::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $this->assertTrue($organization->permissions()->exists());
    }

    #[Test]
    public function it_has_many_roles(): void
    {
        $organization = Organization::factory()->create(['name' => 'Dunder Mifflin']);
        Role::factory()->create([
            'organization_id' => $organization->id,
            'key' => 'assistant_regional_manager',
            'name' => 'Assistant to the Regional Manager',
        ]);

        $this->assertTrue($organization->roles()->exists());
    }

    #[Test]
    public function it_gets_avatar(): void
    {
        $organization = Organization::factory()->create();

        $avatar = $organization->getAvatar();

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $avatar);
    }
}
