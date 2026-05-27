<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\DestroyPatron;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Patron;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DestroyPatronTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_archives_a_patron(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronArchive->value],
        );
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'first_name' => 'Jim',
            'last_name' => 'Halpert',
            'status' => 'active',
        ]);

        new DestroyPatron(
            user: $user,
            organization: $organization,
            patron: $patron,
        )->execute();

        $this->assertDatabaseHas('patrons', [
            'id' => $patron->id,
            'status' => 'archived',
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::PatronArchive
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Archived a patron called Jim Halpert'
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
            permissions: ['random.permission'],
        );
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
        ]);

        new DestroyPatron(
            user: $user,
            organization: $organization,
            patron: $patron,
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
        ]);

        new DestroyPatron(
            user: $user,
            organization: $organization,
            patron: $patron,
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_patron_does_not_belong_to_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronArchive->value],
        );
        $otherPatron = Patron::factory()->create();

        new DestroyPatron(
            user: $user,
            organization: $organization,
            patron: $otherPatron,
        )->execute();
    }
}
