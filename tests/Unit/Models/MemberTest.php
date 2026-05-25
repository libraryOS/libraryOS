<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Member;
use App\Models\Organization;
use App\Models\Permission as PermissionModel;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $member = Member::factory()->create();

        $this->assertTrue($member->user()->exists());
    }

    #[Test]
    public function it_belongs_to_an_organization(): void
    {
        $member = Member::factory()->create();

        $this->assertTrue($member->organization()->exists());
    }

    #[Test]
    public function it_belongs_to_a_role(): void
    {
        $member = Member::factory()->create();

        $this->assertTrue($member->role()->exists());
    }

    #[Test]
    public function it_returns_true_when_member_has_the_permission(): void
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create(['organization_id' => $organization->id]);
        $permission = PermissionModel::factory()->create([
            'organization_id' => $organization->id,
            'key' => 'organization.update',
        ]);
        $role->permissions()->attach($permission);

        $member = Member::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);

        $this->assertTrue($member->hasPermission('organization.update'));
    }

    #[Test]
    public function it_returns_false_when_member_does_not_have_the_permission(): void
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create(['organization_id' => $organization->id]);
        $permission = PermissionModel::factory()->create([
            'organization_id' => $organization->id,
            'key' => 'organization.update',
        ]);
        $role->permissions()->attach($permission);

        $member = Member::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);

        $this->assertFalse($member->hasPermission('organization.delete'));
    }

    #[Test]
    public function it_returns_false_when_member_has_no_role(): void
    {
        $member = Member::factory()->create(['role_id' => null]);

        $this->assertFalse($member->hasPermission('organization.update'));
    }

    #[Test]
    public function it_returns_the_permission_keys_for_the_member(): void
    {
        $organization = Organization::factory()->create();
        $role = Role::factory()->create(['organization_id' => $organization->id]);
        $permissionA = PermissionModel::factory()->create([
            'organization_id' => $organization->id,
            'key' => 'organization.update',
        ]);
        $permissionB = PermissionModel::factory()->create([
            'organization_id' => $organization->id,
            'key' => 'organization.delete',
        ]);
        $role->permissions()->attach([$permissionA->id, $permissionB->id]);

        $member = Member::factory()->create([
            'organization_id' => $organization->id,
            'role_id' => $role->id,
        ]);

        $this->assertEqualsCanonicalizing(
            ['organization.update', 'organization.delete'],
            $member->getPermissions()->all(),
        );
    }

    #[Test]
    public function it_returns_an_empty_array_when_member_has_no_role(): void
    {
        $member = Member::factory()->create(['role_id' => null]);

        $this->assertTrue($member->getPermissions()->isEmpty());
    }
}
