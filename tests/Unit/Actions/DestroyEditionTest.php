<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\DestroyEdition;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Edition;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DestroyEditionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_an_edition(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization($user, $organization, [PermissionEnum::EditionManage->value]);
        $edition = Edition::factory()->create([
            'organization_id' => $organization->id,
            'title' => 'The Hobbit',
        ]);

        new DestroyEdition($user, $organization, $edition)->execute();

        $this->assertDatabaseMissing('editions', [
            'id' => $edition->id,
        ]);
        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::EditionDeletion
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Deleted an edition called The Hobbit'
            ),
        );
    }

    #[Test]
    public function it_throws_an_exception_if_user_does_not_have_the_right_permission(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization($user, $organization, ['random.permission']);
        $edition = Edition::factory()->create(['organization_id' => $organization->id]);

        new DestroyEdition($user, $organization, $edition)->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_edition_does_not_belong_to_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization($user, $organization, [PermissionEnum::EditionManage->value]);
        $edition = Edition::factory()->create(['organization_id' => $this->createOrganization()->id]);

        new DestroyEdition($user, $organization, $edition)->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $edition = Edition::factory()->create(['organization_id' => $organization->id]);

        new DestroyEdition($user, $organization, $edition)->execute();
    }
}
