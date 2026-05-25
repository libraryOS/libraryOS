<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\App\Organization\Adminland;

use App\Enums\PermissionEnum;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_lists_the_roles_of_an_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::RoleManage->value],
        );

        Role::factory()->create([
            'organization_id' => $organization->id,
            'key' => 'editor',
            'name' => 'Editor',
        ]);
        Role::factory()->create([
            'organization_id' => $organization->id,
            'key' => 'viewer',
            'name' => 'Viewer',
        ]);

        $response = $this->actingAs($user)->get('/organizations/'.$organization->slug.'/adminland/roles');

        $response->assertStatus(200);
        $response->assertViewIs('app.organization.adminland.roles.index');
        $response->assertViewHas(
            'roles',
            fn ($roles): bool => $roles->count() === 2,
        );
    }

    #[Test]
    public function it_does_not_show_roles_from_other_organizations(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::RoleManage->value],
        );

        Role::factory()->create([
            'organization_id' => $organization->id,
            'key' => 'editor',
            'name' => 'Editor',
        ]);

        $otherUser = $this->createUser();
        $otherOrganization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $otherUser,
            organization: $otherOrganization,
            permissions: [PermissionEnum::RoleManage->value],
        );
        Role::factory()->create([
            'organization_id' => $otherOrganization->id,
            'key' => 'viewer',
            'name' => 'Viewer',
        ]);

        $response = $this->actingAs($user)->get('/organizations/'.$organization->slug.'/adminland/roles');

        $response->assertStatus(200);
        $response->assertViewHas(
            'roles',
            fn ($roles): bool => $roles->count() === 1,
        );
    }
}
