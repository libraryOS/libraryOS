<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreatePatronType;
use App\Enums\PermissionEnum;
use App\Jobs\LogUserAction;
use App\Models\PatronType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreatePatronTypeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_patron_type(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );

        $patronType = new CreatePatronType(
            user: $user,
            organization: $organization,
            key: 'adult',
            name: 'Adult',
            description: 'Standard adult membership',
            isActive: true,
            membershipDurationDays: 365,
            maxLoans: 5,
            keepLoanHistory: false,
            canReceiveNotifications: true,
            minimumAge: 18,
        )->execute();

        $this->assertInstanceOf(PatronType::class, $patronType);

        $this->assertDatabaseHas('patron_types', [
            'id' => $patronType->id,
            'organization_id' => $organization->id,
            'key' => 'adult',
            'name' => 'Adult',
            'description' => 'Standard adult membership',
            'is_active' => true,
            'membership_duration_days' => 365,
            'max_loans' => 5,
            'keep_loan_history' => false,
            'can_receive_notifications' => true,
            'minimum_age' => 18,
            'maximum_age' => null,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === 'patron_type_creation'
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Created a patron type called Adult'
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

        new CreatePatronType(
            user: $user,
            organization: $organization,
            key: 'adult',
            name: 'Adult',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $otherOrganization = $this->createOrganization();

        new CreatePatronType(
            user: $user,
            organization: $otherOrganization,
            key: 'adult',
            name: 'Adult',
        )->execute();
    }
}
