<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreateBranch;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Branch;
use App\Models\Country;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateBranchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_branch(): void
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

        $branch = new CreateBranch(
            user: $user,
            organization: $organization,
            name: 'Main Office',
            addressLine1: '1725 Slough Avenue',
            addressLine2: 'Floor 2',
            city: 'Scranton',
            stateProvince: 'PA',
            postalCode: '18505',
            timezone: 'America/New_York',
            code: 'HQ-001',
            countryId: $country->id,
        )->execute();

        $this->assertInstanceOf(Branch::class, $branch);

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'organization_id' => $organization->id,
            'country_id' => $country->id,
            'name' => 'Main Office',
            'code' => 'HQ-001',
            'address_line_1' => '1725 Slough Avenue',
            'address_line_2' => 'Floor 2',
            'city' => 'Scranton',
            'state_province' => 'PA',
            'postal_code' => '18505',
            'timezone' => 'America/New_York',
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::BranchCreation
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Created a branch called Main Office'
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

        new CreateBranch(
            user: $user,
            organization: $organization,
            name: 'Main Office',
            addressLine1: '1725 Slough Avenue',
            addressLine2: null,
            city: 'Scranton',
            stateProvince: null,
            postalCode: null,
            timezone: null,
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $otherOrganization = $this->createOrganization();

        new CreateBranch(
            user: $user,
            organization: $otherOrganization,
            name: 'Main Office',
            addressLine1: '1725 Slough Avenue',
            addressLine2: null,
            city: 'Scranton',
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

        new CreateBranch(
            user: $user,
            organization: $organization,
            name: 'Main Office',
            addressLine1: '1725 Slough Avenue',
            addressLine2: null,
            city: 'Scranton',
            stateProvince: null,
            postalCode: null,
            timezone: null,
            countryId: 999_999,
        )->execute();
    }
}
