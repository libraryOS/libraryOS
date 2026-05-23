<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreateRole;
use App\Enums\Permission;
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
        $organization = $this->addOrganization($user);

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
    public function it_creates_a_role_when_user_is_admin(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->addOrganization(
            user: $user,
            permission: Permission::Admin,
        );

        $role = new CreateRole(
            user: $user,
            organization: $organization,
            key: 'archivist',
            name: 'Archivist',
        )->execute();

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'organization_id' => $organization->id,
            'key' => 'archivist',
            'name' => 'Archivist',
        ]);
    }

    #[Test]
    public function it_creates_a_role_without_a_description(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->addOrganization($user);

        $role = new CreateRole(
            user: $user,
            organization: $organization,
            key: 'reader',
            name: 'Reader',
        )->execute();

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'organization_id' => $organization->id,
            'description' => null,
        ]);
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $otherOrganization = $this->addOrganization($this->createUser());

        new CreateRole(
            user: $user,
            organization: $otherOrganization,
            key: 'librarian',
            name: 'Librarian',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_a_member(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->addOrganization(
            user: $user,
            permission: Permission::Member,
        );

        new CreateRole(
            user: $user,
            organization: $organization,
            key: 'librarian',
            name: 'Librarian',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_a_guest(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->addOrganization(
            user: $user,
            permission: Permission::Guest,
        );

        new CreateRole(
            user: $user,
            organization: $organization,
            key: 'librarian',
            name: 'Librarian',
        )->execute();
    }
}
