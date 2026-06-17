<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\DestroyWork;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Organization;
use App\Models\Work;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DestroyWorkTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_a_work(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::WorkManage->value],
        );
        $work = Work::factory()->create([
            'organization_id' => $organization->id,
            'title' => 'The Hobbit',
        ]);

        new DestroyWork(
            user: $user,
            organization: $organization,
            work: $work,
        )->execute();

        $this->assertDatabaseMissing('works', [
            'id' => $work->id,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::WorkDeletion
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Deleted a work called The Hobbit'
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
        $work = Work::factory()->create([
            'organization_id' => $organization->id,
        ]);

        new DestroyWork(
            user: $user,
            organization: $organization,
            work: $work,
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_work_does_not_belong_to_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::WorkManage->value],
        );
        $otherOrganization = $this->createOrganization();
        $work = Work::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        new DestroyWork(
            user: $user,
            organization: $organization,
            work: $work,
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
            organization: Organization::factory()->create(),
            permissions: [PermissionEnum::WorkManage->value],
        );
        $work = Work::factory()->create([
            'organization_id' => $organization->id,
        ]);

        new DestroyWork(
            user: $user,
            organization: $organization,
            work: $work,
        )->execute();
    }
}
