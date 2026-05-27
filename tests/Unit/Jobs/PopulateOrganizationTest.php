<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\PopulateOrganization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PopulateOrganizationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_default_roles(): void
    {
        $this->createUser();
        $organization = $this->createOrganization();

        new PopulateOrganization($organization)->handle();

        $this->assertDatabaseHas('roles', ['organization_id' => $organization->id, 'key' => 'owner', 'name_translation_key' => 'role_owner']);
        $this->assertDatabaseHas('roles', ['organization_id' => $organization->id, 'key' => 'administrator', 'name_translation_key' => 'role_administrator']);

        $this->assertEquals(2, $organization->roles()->count());
    }

    #[Test]
    public function it_creates_default_permissions(): void
    {
        $this->createUser();
        $organization = $this->createOrganization();

        new PopulateOrganization($organization)->handle();

        $this->assertDatabaseHas('permissions', ['organization_id' => $organization->id, 'key' => 'organization.update', 'name_translation_key' => 'Update organization']);
        $this->assertDatabaseHas('permissions', ['organization_id' => $organization->id, 'key' => 'organization.delete', 'name_translation_key' => 'Delete organization']);
        $this->assertDatabaseHas('permissions', ['organization_id' => $organization->id, 'key' => 'item_type.manage', 'name_translation_key' => 'Manage item types']);
        $this->assertDatabaseHas('permissions', ['organization_id' => $organization->id, 'key' => 'patron_type.manage', 'name_translation_key' => 'Manage patron types']);
        $this->assertDatabaseHas('permissions', ['organization_id' => $organization->id, 'key' => 'location.manage', 'name_translation_key' => 'Manage locations']);

        $this->assertEquals(8, $organization->permissions()->count());
    }

    #[Test]
    public function it_maps_default_permissions_to_roles(): void
    {
        $this->createUser();
        $organization = $this->createOrganization();

        new PopulateOrganization($organization)->handle();

        $owner = $organization->roles()->where('key', 'owner')->first();
        $administrator = $organization->roles()->where('key', 'administrator')->first();

        $this->assertCount(8, $owner->permissions);
        $this->assertCount(7, $administrator->permissions);
    }

    #[Test]
    public function it_maps_default_item_types(): void
    {
        $this->createUser();
        $organization = $this->createOrganization();

        new PopulateOrganization($organization)->handle();

        $itemTypes = $organization->itemTypes()->get();

        $this->assertCount(7, $itemTypes);
        $this->assertEquals('book', $itemTypes->first()->key);
    }

    #[Test]
    public function it_creates_default_patron_types(): void
    {
        $this->createUser();
        $organization = $this->createOrganization();

        new PopulateOrganization($organization)->handle();

        $patronTypes = $organization->patronTypes()->orderBy('key')->get();

        $this->assertCount(6, $patronTypes);
        $this->assertDatabaseHas('patron_types', ['organization_id' => $organization->id, 'key' => 'adult', 'name_translation_key' => 'Adult']);
        $this->assertDatabaseHas('patron_types', ['organization_id' => $organization->id, 'key' => 'child', 'name_translation_key' => 'Child']);
        $this->assertDatabaseHas('patron_types', ['organization_id' => $organization->id, 'key' => 'student', 'name_translation_key' => 'Student']);
        $this->assertDatabaseHas('patron_types', ['organization_id' => $organization->id, 'key' => 'teacher', 'name_translation_key' => 'Teacher']);
        $this->assertDatabaseHas('patron_types', ['organization_id' => $organization->id, 'key' => 'staff', 'name_translation_key' => 'Staff']);
        $this->assertDatabaseHas('patron_types', ['organization_id' => $organization->id, 'key' => 'temporary', 'name_translation_key' => 'Temporary']);
    }
}
