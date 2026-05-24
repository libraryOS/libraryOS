<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Member;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_an_organization(): void
    {
        $organization = Organization::factory()->create(['name' => 'Dunder Mifflin']);
        $role = Role::factory()->create([
            'organization_id' => $organization->id,
            'key' => 'regional_manager',
            'name' => 'Regional Manager',
        ]);

        $this->assertTrue($role->organization()->exists());
    }

    #[Test]
    public function it_can_use_a_translation_key_instead_of_a_name(): void
    {
        $role = Role::factory()->create([
            'key' => 'receptionist',
            'name' => null,
            'name_translation_key' => 'roles.receptionist',
        ]);

        $this->assertNull($role->name);
        $this->assertEquals('roles.receptionist', $role->name_translation_key);
    }

    #[Test]
    public function it_returns_name_when_set(): void
    {
        $role = Role::factory()->make([
            'name' => 'Regional Manager',
            'name_translation_key' => null,
        ]);

        $this->assertEquals('Regional Manager', $role->getName());
    }

    #[Test]
    public function it_returns_translated_name_when_name_is_null(): void
    {
        $role = Role::factory()->make([
            'name' => null,
            'name_translation_key' => 'Receptionist',
        ]);

        $this->assertEquals('Receptionist', $role->getName());
    }

    #[Test]
    public function it_belongs_to_many_permissions(): void
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create(['organization_id' => $organization->id]);
        $permission = Permission::factory()->create(['organization_id' => $organization->id]);

        $role->permissions()->attach($permission);

        $this->assertTrue($role->permissions()->where('permissions.id', $permission->id)->exists());
    }

    #[Test]
    public function it_has_many_members(): void
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create(['organization_id' => $organization->id]);
        Member::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);

        $this->assertTrue($role->members()->exists());
    }
}
