<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreateWork;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Work;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateWorkTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_work(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::WorkManage->value],
        );

        $work = new CreateWork(
            user: $user,
            organization: $organization,
            title: 'The Hobbit',
            subtitle: 'There and Back Again',
            description: 'A fantasy novel',
        )->execute();

        $this->assertInstanceOf(Work::class, $work);

        $this->assertDatabaseHas('works', [
            'id' => $work->id,
            'organization_id' => $organization->id,
            'title' => 'The Hobbit',
            'subtitle' => 'There and Back Again',
            'description' => 'A fantasy novel',
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::WorkCreation
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Created a work called The Hobbit'
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

        new CreateWork(
            user: $user,
            organization: $organization,
            title: 'The Hobbit',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $otherOrganization = $this->createOrganization();

        new CreateWork(
            user: $user,
            organization: $otherOrganization,
            title: 'The Hobbit',
        )->execute();
    }
}
