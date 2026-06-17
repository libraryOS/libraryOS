<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\Edition;
use App\Models\ItemType;
use App\Models\Location;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Patron;
use App\Models\PatronLog;
use App\Models\PatronType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Work;
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
    public function it_has_many_patrons(): void
    {
        $organization = Organization::factory()->create();
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);
        Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);

        $this->assertTrue($organization->patrons()->exists());
    }

    #[Test]
    public function it_has_many_works(): void
    {
        $organization = Organization::factory()->create();
        Work::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $this->assertTrue($organization->works()->exists());
    }

    #[Test]
    public function it_has_many_editions(): void
    {
        $organization = Organization::factory()->create();
        Edition::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $this->assertTrue($organization->editions()->exists());
    }

    #[Test]
    public function it_has_many_patron_logs(): void
    {
        $organization = Organization::factory()->create();
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);

        PatronLog::factory()->create([
            'organization_id' => $organization->id,
            'patron_id' => $patron->id,
        ]);

        $this->assertTrue($organization->patronLogs()->exists());
    }

    #[Test]
    public function it_has_many_locations(): void
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        $this->assertTrue($organization->locations()->exists());
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
