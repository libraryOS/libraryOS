<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\UpdateBranch;
use App\Enums\PermissionEnum;
use App\Jobs\LogUserAction;
use App\Models\Branch;
use App\Models\Country;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateBranchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_a_branch(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value]
        );
        $country = Country::factory()->create();
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
            'name' => 'Old Branch',
            'address_line_1' => 'Old Address',
            'city' => 'Old City',
        ]);

        $updatedBranch = new UpdateBranch(
            user: $user,
            organization: $organization,
            branch: $branch,
            name: 'New Branch',
            addressLine1: 'New Address',
            addressLine2: 'Suite 100',
            city: 'New City',
            stateProvince: 'PA',
            postalCode: '18505',
            timezone: 'America/New_York',
            countryId: $country->id,
        )->execute();

        $this->assertSame('New Branch', $updatedBranch->name);

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'country_id' => $country->id,
            'name' => 'New Branch',
            'address_line_1' => 'New Address',
            'address_line_2' => 'Suite 100',
            'city' => 'New City',
            'state_province' => 'PA',
            'postal_code' => '18505',
            'timezone' => 'America/New_York',
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === 'branch_update'
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Updated a branch called New Branch'
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

        new UpdateBranch(
            user: $user,
            organization: $organization,
            branch: $branch,
            name: 'New Branch',
            addressLine1: 'New Address',
            addressLine2: null,
            city: 'New City',
            stateProvince: null,
            postalCode: null,
            timezone: null,
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

        new UpdateBranch(
            user: $user,
            organization: $organization,
            branch: $branch,
            name: 'New Branch',
            addressLine1: 'New Address',
            addressLine2: null,
            city: 'New City',
            stateProvince: null,
            postalCode: null,
            timezone: null,
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_country_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

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
        ]);

        new UpdateBranch(
            user: $user,
            organization: $organization,
            branch: $branch,
            name: 'New Office',
            addressLine1: 'New Address',
            addressLine2: null,
            city: 'New City',
            stateProvince: null,
            postalCode: null,
            timezone: null,
            countryId: 999_999,
        )->execute();
    }
}
