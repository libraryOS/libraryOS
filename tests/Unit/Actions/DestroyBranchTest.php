<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\DestroyBranch;
use App\Enums\PermissionEnum;
use App\Jobs\LogUserAction;
use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DestroyBranchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_a_branch(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value]
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
            'name' => 'Main Branch',
        ]);

        new DestroyBranch(
            user: $user,
            organization: $organization,
            branch: $branch,
        )->execute();

        $this->assertDatabaseMissing('branches', [
            'id' => $branch->id,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === 'branch_deletion'
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Deleted a branch called Main Branch'
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

        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);

        new DestroyBranch(
            user: $user,
            organization: $organization,
            branch: $branch,
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
            permissions: [PermissionEnum::BranchManage->value]
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);

        new DestroyBranch(
            user: $user,
            organization: $organization,
            branch: $branch,
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_branch_does_not_belong_to_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value]
        );
        $otherOrganization = $this->createOrganization();
        $branch = Branch::factory()->create([
            'organization_id' => $otherOrganization->id,
            'country_id' => null,
        ]);

        new DestroyBranch(
            user: $user,
            organization: $organization,
            branch: $branch,
        )->execute();
    }
}
