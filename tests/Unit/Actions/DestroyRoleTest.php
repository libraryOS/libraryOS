<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\DestroyRole;
use App\Enums\PermissionEnum;
use App\Jobs\LogUserAction;
use App\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DestroyRoleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_destroys_a_role(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::RoleManage->value]
        );
        $role = Role::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Librarian',
            'is_system' => false,
        ]);

        new DestroyRole(
            user: $user,
            organization: $organization,
            role: $role,
        )->execute();

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === 'role_deletion'
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Deleted a role called Librarian'
            ),
        );
    }

    #[Test]
    public function it_throws_an_exception_if_the_role_belongs_to_another_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::RoleManage->value]
        );
        $otherOrganization = $this->createOrganization();
        $role = Role::factory()->create([
            'organization_id' => $otherOrganization->id,
            'is_system' => false,
        ]);

        new DestroyRole(
            user: $user,
            organization: $organization,
            role: $role,
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_the_role_is_a_system_role(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::RoleManage->value]
        );
        $role = Role::factory()->create([
            'organization_id' => $organization->id,
            'is_system' => true,
        ]);

        new DestroyRole(
            user: $user,
            organization: $organization,
            role: $role,
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::RoleManage->value]
        );
        $otherOrganization = $this->createOrganization();
        $role = Role::factory()->create([
            'organization_id' => $otherOrganization->id,
            'is_system' => false,
        ]);

        new DestroyRole(
            user: $user,
            organization: $otherOrganization,
            role: $role,
        )->execute();
    }
}
