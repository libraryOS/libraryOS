<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\UpdateLocation;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Branch;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateLocationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_a_location(): void
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
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'name' => 'Old Name',
        ]);

        $updatedLocation = new UpdateLocation(
            user: $user,
            organization: $organization,
            location: $location,
            branchId: $branch->id,
            name: 'New Name',
            code: 'NEW-001',
            description: 'Updated description.',
            isActive: false,
            isPublic: false,
            supportsPickups: true,
            supportsReturns: true,
        )->execute();

        $this->assertInstanceOf(Location::class, $updatedLocation);

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'New Name',
            'code' => 'NEW-001',
            'description' => 'Updated description.',
            'is_active' => false,
            'is_public' => false,
            'supports_pickups' => true,
            'supports_returns' => true,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::LocationUpdate
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Updated a location called New Name'
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
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        new UpdateLocation(
            user: $user,
            organization: $organization,
            location: $location,
            branchId: $branch->id,
            name: 'New Name',
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
            permissions: [PermissionEnum::LocationManage->value]
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        new UpdateLocation(
            user: $user,
            organization: $organization,
            location: $location,
            branchId: $branch->id,
            name: 'New Name',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_location_does_not_belong_to_the_organization(): void
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

        new UpdateLocation(
            user: $user,
            organization: $organization,
            location: $otherLocation,
            branchId: $branch->id,
            name: 'New Name',
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
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);
        $otherBranch = Branch::factory()->create(['country_id' => null]);

        new UpdateLocation(
            user: $user,
            organization: $organization,
            location: $location,
            branchId: $otherBranch->id,
            name: 'New Name',
        )->execute();
    }
}
