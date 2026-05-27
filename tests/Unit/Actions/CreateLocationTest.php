<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreateLocation;
use App\Enums\PermissionEnum;
use App\Jobs\LogUserAction;
use App\Models\Branch;
use App\Models\Location;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateLocationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_location(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value]
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);

        $location = new CreateLocation(
            user: $user,
            organization: $organization,
            branchId: $branch->id,
            name: 'Main Stacks',
            code: 'STK-001',
            description: 'The main stacks area.',
            isActive: true,
            isPublic: true,
            supportsPickups: true,
            supportsReturns: true,
        )->execute();

        $this->assertInstanceOf(Location::class, $location);

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'parent_id' => null,
            'name' => 'Main Stacks',
            'code' => 'STK-001',
            'description' => 'The main stacks area.',
            'is_active' => true,
            'is_public' => true,
            'supports_pickups' => true,
            'supports_returns' => true,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === 'location_creation'
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Created a location called Main Stacks'
            ),
        );
    }

    #[Test]
    public function it_creates_a_location_with_a_parent(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value]
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $parent = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        $location = new CreateLocation(
            user: $user,
            organization: $organization,
            branchId: $branch->id,
            name: 'Sub-section A',
            parentId: $parent->id,
        )->execute();

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'parent_id' => $parent->id,
        ]);
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

        new CreateLocation(
            user: $user,
            organization: $organization,
            branchId: $branch->id,
            name: 'Main Stacks',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);

        new CreateLocation(
            user: $user,
            organization: $organization,
            branchId: $branch->id,
            name: 'Main Stacks',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_branch_does_not_belong_to_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value]
        );
        $otherBranch = Branch::factory()->create(['country_id' => null]);

        new CreateLocation(
            user: $user,
            organization: $organization,
            branchId: $otherBranch->id,
            name: 'Main Stacks',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_parent_location_does_not_belong_to_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value]
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $otherLocation = Location::factory()->create();

        new CreateLocation(
            user: $user,
            organization: $organization,
            branchId: $branch->id,
            name: 'Sub-section',
            parentId: $otherLocation->id,
        )->execute();
    }
}
