<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreateRole;
use App\Enums\PermissionEnum;
use App\Jobs\LogUserAction;
use App\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateRoleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_role(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::RoleManage->value]
        );

        $role = new CreateRole(
            user: $user,
            organization: $organization,
            key: 'librarian',
            name: 'Librarian',
            description: 'Manages the library catalog.',
        )->execute();

        $this->assertInstanceOf(Role::class, $role);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'organization_id' => $organization->id,
            'key' => 'librarian',
            'name' => 'Librarian',
            'description' => 'Manages the library catalog.',
            'is_system' => false,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === 'role_creation'
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Created a role called Librarian'
            ),
        );
    }

    #[Test]
    public function it_throws_an_exception_if_user_does_not_have_the_right_permission(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: ['random.permission']
        );

        new CreateRole(
            user: $user,
            organization: $organization,
            key: 'librarian',
            name: 'Librarian',
            description: 'Manages the library catalog.',
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

        new CreateRole(
            user: $user,
            organization: $otherOrganization,
            key: 'librarian',
            name: 'Librarian',
        )->execute();
    }
}
