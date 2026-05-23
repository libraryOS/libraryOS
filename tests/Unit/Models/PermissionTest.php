<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_an_organization(): void
    {
        $permission = Permission::factory()->create();

        $this->assertTrue($permission->organization()->exists());
    }

    #[Test]
    public function it_belongs_to_many_roles(): void
    {
        $permission = Permission::factory()->create();
        $role = Role::factory()->create(['organization_id' => $permission->organization_id]);

        $permission->roles()->attach($role);

        $this->assertTrue($permission->roles()->where('roles.id', $role->id)->exists());
    }

    #[Test]
    public function it_can_belong_to_multiple_roles(): void
    {
        $permission = Permission::factory()->create();
        $roles = Role::factory()->count(3)->create(['organization_id' => $permission->organization_id]);

        $permission->roles()->attach($roles->pluck('id'));

        $this->assertCount(3, $permission->roles);
    }
}
