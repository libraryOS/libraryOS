<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\UpdatePatron;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Patron;
use App\Models\PatronType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdatePatronTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_a_patron(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $linkedUser = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronUpdate->value],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => $branch->id,
            'first_name' => 'Old',
            'last_name' => 'Name',
        ]);

        $updated = new UpdatePatron(
            user: $user,
            organization: $organization,
            patron: $patron,
            userId: $linkedUser->id,
            patronTypeId: $patronType->id,
            homeBranchId: $branch->id,
            cardNumber: 'CARD-999',
            firstName: 'Dwight',
            lastName: 'Schrute',
            email: 'dwight@example.com',
            phone: '555-1111',
            status: 'active',
            membershipExpiresAt: '2028-01-01 00:00:00',
            notes: 'Updated notes',
        )->execute();

        $this->assertInstanceOf(Patron::class, $updated);

        $this->assertDatabaseHas('patrons', [
            'id' => $patron->id,
            'user_id' => $linkedUser->id,
            'card_number' => 'CARD-999',
            'first_name' => 'Dwight',
            'last_name' => 'Schrute',
            'email' => 'dwight@example.com',
            'phone' => '555-1111',
            'status' => 'active',
            'notes' => 'Updated notes',
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::PatronUpdate
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Updated a patron called Dwight Schrute'
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
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);

        new UpdatePatron(
            user: $user,
            organization: $organization,
            patron: $patron,
            userId: null,
            patronTypeId: $patronType->id,
            homeBranchId: null,
            cardNumber: 'CARD-999',
            firstName: 'Dwight',
            lastName: 'Schrute',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $otherOrganization = Organization::factory()->create();
        $this->assignUserToOrganization(
            user: $user,
            organization: $otherOrganization,
            permissions: [PermissionEnum::PatronUpdate->value],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);

        new UpdatePatron(
            user: $user,
            organization: $organization,
            patron: $patron,
            userId: null,
            patronTypeId: $patronType->id,
            homeBranchId: null,
            cardNumber: 'CARD-999',
            firstName: 'Dwight',
            lastName: 'Schrute',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_patron_does_not_belong_to_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronUpdate->value],
        );
        $otherPatron = Patron::factory()->create();
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        new UpdatePatron(
            user: $user,
            organization: $organization,
            patron: $otherPatron,
            userId: null,
            patronTypeId: $patronType->id,
            homeBranchId: null,
            cardNumber: 'CARD-999',
            firstName: 'Dwight',
            lastName: 'Schrute',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_patron_type_does_not_belong_to_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronUpdate->value],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);
        $otherPatronType = PatronType::factory()->create();

        new UpdatePatron(
            user: $user,
            organization: $organization,
            patron: $patron,
            userId: null,
            patronTypeId: $otherPatronType->id,
            homeBranchId: null,
            cardNumber: 'CARD-999',
            firstName: 'Dwight',
            lastName: 'Schrute',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_home_branch_does_not_belong_to_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronUpdate->value],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);
        $otherBranch = Branch::factory()->create(['country_id' => null]);

        new UpdatePatron(
            user: $user,
            organization: $organization,
            patron: $patron,
            userId: null,
            patronTypeId: $patronType->id,
            homeBranchId: $otherBranch->id,
            cardNumber: 'CARD-999',
            firstName: 'Dwight',
            lastName: 'Schrute',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_linked_user_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronUpdate->value],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);

        new UpdatePatron(
            user: $user,
            organization: $organization,
            patron: $patron,
            userId: 999999,
            patronTypeId: $patronType->id,
            homeBranchId: null,
            cardNumber: 'CARD-999',
            firstName: 'Dwight',
            lastName: 'Schrute',
        )->execute();
    }
}
