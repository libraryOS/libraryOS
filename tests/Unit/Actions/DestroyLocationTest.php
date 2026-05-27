<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\DestroyLocation;
use App\Enums\PermissionEnum;
use App\Jobs\LogUserAction;
use App\Models\Branch;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DestroyLocationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_a_location(): void
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
            'name' => 'Main Stacks',
        ]);

        new DestroyLocation(
            user: $user,
            organization: $organization,
            location: $location,
        )->execute();

        $this->assertDatabaseMissing('locations', [
            'id' => $location->id,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === 'location_deletion'
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Deleted a location called Main Stacks'
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

        new DestroyLocation(
            user: $user,
            organization: $organization,
            location: $location,
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

        new DestroyLocation(
            user: $user,
            organization: $organization,
            location: $location,
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
        $otherLocation = Location::factory()->create();

        new DestroyLocation(
            user: $user,
            organization: $organization,
            location: $otherLocation,
        )->execute();
    }
}
