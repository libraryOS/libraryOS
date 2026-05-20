<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Organization;
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
    public function it_can_be_a_system_role_without_an_organization(): void
    {
        $role = Role::factory()->system()->create([
            'key' => 'librarian',
            'name' => 'Librarian',
        ]);

        $this->assertNull($role->organization_id);
        $this->assertTrue($role->is_system);
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
}
