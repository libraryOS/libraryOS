<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\UpdateWork;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Work;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateWorkTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_a_work(): void
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
            'title' => 'Old Title',
            'subtitle' => null,
            'description' => null,
        ]);

        $updated = new UpdateWork(
            user: $user,
            organization: $organization,
            work: $work,
            title: 'The Fellowship of the Ring',
            subtitle: 'The Lord of the Rings: Part One',
            description: 'A fantasy novel',
        )->execute();

        $this->assertSame('The Fellowship of the Ring', $updated->title);

        $this->assertDatabaseHas('works', [
            'id' => $work->id,
            'title' => 'The Fellowship of the Ring',
            'subtitle' => 'The Lord of the Rings: Part One',
            'description' => 'A fantasy novel',
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::WorkUpdate
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Updated a work called The Fellowship of the Ring'
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

        new UpdateWork(
            user: $user,
            organization: $organization,
            work: $work,
            title: 'The Fellowship of the Ring',
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

        new UpdateWork(
            user: $user,
            organization: $organization,
            work: $work,
            title: 'The Fellowship of the Ring',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $work = Work::factory()->create([
            'organization_id' => $organization->id,
        ]);

        new UpdateWork(
            user: $user,
            organization: $organization,
            work: $work,
            title: 'The Fellowship of the Ring',
        )->execute();
    }
}
