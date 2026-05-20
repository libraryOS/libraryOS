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
        $user = $this->createUser();
        $organization = $this->addOrganization($user);

        new PopulateOrganization($organization)->handle();

        $this->assertDatabaseHas('roles', ['organization_id' => $organization->id, 'key' => 'owner', 'name_translation_key' => 'role_owner']);
        $this->assertDatabaseHas('roles', ['organization_id' => $organization->id, 'key' => 'administrator', 'name_translation_key' => 'role_administrator']);

        $this->assertEquals(2, $organization->roles()->count());
    }

    #[Test]
    public function it_creates_default_permissions(): void
    {
        $user = $this->createUser();
        $organization = $this->addOrganization($user);

        new PopulateOrganization($organization)->handle();

        $this->assertDatabaseHas('permissions', ['organization_id' => $organization->id, 'key' => 'organization.view', 'name' => 'View organization', 'is_system' => true]);
        $this->assertDatabaseHas('permissions', ['organization_id' => $organization->id, 'key' => 'organization.update', 'name' => 'Update organization', 'is_system' => true]);

        $this->assertEquals(2, $organization->permissions()->count());
    }

    #[Test]
    public function it_creates_default_office_types(): void
    {
        $user = $this->createUser();
        $organization = $this->addOrganization($user);

        new PopulateOrganization($organization)->handle();

        $this->assertDatabaseHas('office_types', ['organization_id' => $organization->id, 'name' => 'Headquarters', 'position' => 0]);
        $this->assertDatabaseHas('office_types', ['organization_id' => $organization->id, 'name' => 'Office', 'position' => 1]);
        $this->assertDatabaseHas('office_types', ['organization_id' => $organization->id, 'name' => 'Remote', 'position' => 2]);
        $this->assertDatabaseHas('office_types', ['organization_id' => $organization->id, 'name' => 'Coworking', 'position' => 3]);
        $this->assertDatabaseHas('office_types', ['organization_id' => $organization->id, 'name' => 'Other', 'position' => 4]);

        $this->assertEquals(5, $organization->officeTypes()->count());
    }

    #[Test]
    public function it_creates_default_member_types(): void
    {
        $user = $this->createUser();
        $organization = $this->addOrganization($user);

        new PopulateOrganization($organization)->handle();

        $this->assertDatabaseHas('member_types', ['organization_id' => $organization->id, 'name' => 'Member', 'position' => 0]);
        $this->assertDatabaseHas('member_types', ['organization_id' => $organization->id, 'name' => 'Employee', 'position' => 1]);
        $this->assertDatabaseHas('member_types', ['organization_id' => $organization->id, 'name' => 'Student', 'position' => 2]);
        $this->assertDatabaseHas('member_types', ['organization_id' => $organization->id, 'name' => 'Freelance', 'position' => 3]);

        $this->assertEquals(4, $organization->memberTypes()->count());
    }
}
