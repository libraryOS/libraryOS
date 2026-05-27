<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\UpdatePatronType;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\PatronType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdatePatronTypeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_a_patron_type(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
            'key' => 'old-key',
            'name' => 'Old Name',
        ]);

        $updated = new UpdatePatronType(
            user: $user,
            organization: $organization,
            patronType: $patronType,
            key: 'senior',
            name: 'Senior',
            description: 'Senior citizen membership',
            isActive: true,
            membershipDurationDays: 365,
            maxLoans: 3,
            keepLoanHistory: true,
            canReceiveNotifications: false,
            minimumAge: 65,
        )->execute();

        $this->assertSame('Senior', $updated->name);

        $this->assertDatabaseHas('patron_types', [
            'id' => $patronType->id,
            'key' => 'senior',
            'name' => 'Senior',
            'description' => 'Senior citizen membership',
            'is_active' => true,
            'membership_duration_days' => 365,
            'max_loans' => 3,
            'keep_loan_history' => true,
            'can_receive_notifications' => false,
            'minimum_age' => 65,
            'maximum_age' => null,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::PatronTypeUpdate
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Updated a patron type called Senior'
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
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        new UpdatePatronType(
            user: $user,
            organization: $organization,
            patronType: $patronType,
            key: 'senior',
            name: 'Senior',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_patron_type_does_not_belong_to_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );
        $otherOrganization = $this->createOrganization();
        $patronType = PatronType::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        new UpdatePatronType(
            user: $user,
            organization: $organization,
            patronType: $patronType,
            key: 'senior',
            name: 'Senior',
        )->execute();
    }
}
