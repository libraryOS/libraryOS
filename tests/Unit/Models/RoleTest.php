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
}
