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
        $organization = $this->createOrganization();

        new PopulateOrganization($organization)->handle();

        $this->assertDatabaseHas('roles', ['organization_id' => $organization->id, 'key' => 'owner', 'name_translation_key' => 'role_owner']);
        $this->assertDatabaseHas('roles', ['organization_id' => $organization->id, 'key' => 'administrator', 'name_translation_key' => 'role_administrator']);

        $this->assertEquals(2, $organization->roles()->count());
    }

    #[Test]
    public function it_creates_default_permissions(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();

        new PopulateOrganization($organization)->handle();

        $this->assertDatabaseHas('permissions', ['organization_id' => $organization->id, 'key' => 'organization.update', 'name_translation_key' => 'Update organization']);
        $this->assertDatabaseHas('permissions', ['organization_id' => $organization->id, 'key' => 'organization.delete', 'name_translation_key' => 'Delete organization']);

        $this->assertEquals(5, $organization->permissions()->count());
    }

    #[Test]
    public function it_maps_default_permissions_to_roles(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();

        new PopulateOrganization($organization)->handle();

        $owner = $organization->roles()->where('key', 'owner')->first();
        $administrator = $organization->roles()->where('key', 'administrator')->first();

        $this->assertCount(5, $owner->permissions);
        $this->assertCount(4, $administrator->permissions);
    }
}
