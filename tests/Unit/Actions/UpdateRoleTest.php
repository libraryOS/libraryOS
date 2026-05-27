<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\UpdateRole;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateRoleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_a_role(): void
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
            'description' => 'Old description.',
            'is_system' => false,
        ]);

        $updatedRole = new UpdateRole(
            user: $user,
            organization: $organization,
            role: $role,
            name: 'Senior Librarian',
            description: 'Manages the library catalog.',
        )->execute();

        $this->assertInstanceOf(Role::class, $updatedRole);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'organization_id' => $organization->id,
            'name' => 'Senior Librarian',
            'description' => 'Manages the library catalog.',
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::RoleUpdate
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Updated a role called Senior Librarian'
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

        new UpdateRole(
            user: $user,
            organization: $organization,
            role: $role,
            name: 'Librarian',
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

        new UpdateRole(
            user: $user,
            organization: $otherOrganization,
            role: $role,
            name: 'Librarian',
        )->execute();
    }
}
