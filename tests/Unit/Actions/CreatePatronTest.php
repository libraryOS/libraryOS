<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreatePatron;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Branch;
use App\Models\Patron;
use App\Models\PatronType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreatePatronTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_patron(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $linkedUser = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronCreate->value],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $patron = new CreatePatron(
            user: $user,
            organization: $organization,
            userId: $linkedUser->id,
            patronTypeId: $patronType->id,
            homeBranchId: $branch->id,
            cardNumber: 'P-123',
            firstName: 'Pam',
            lastName: 'Beesly',
            email: 'pam@example.com',
            phone: '555-1000',
            status: 'active',
            membershipExpiresAt: '2027-01-01 00:00:00',
            notes: 'Frequent visitor',
        )->execute();

        $this->assertInstanceOf(Patron::class, $patron);

        $this->assertDatabaseHas('patrons', [
            'id' => $patron->id,
            'organization_id' => $organization->id,
            'user_id' => $linkedUser->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => $branch->id,
            'card_number' => 'P-123',
            'first_name' => 'Pam',
            'last_name' => 'Beesly',
            'email' => 'pam@example.com',
            'phone' => '555-1000',
            'status' => 'active',
            'notes' => 'Frequent visitor',
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::PatronCreation
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Created a patron called Pam Beesly'
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

        new CreatePatron(
            user: $user,
            organization: $organization,
            userId: null,
            patronTypeId: $patronType->id,
            homeBranchId: null,
            cardNumber: 'P-123',
            firstName: 'Pam',
            lastName: 'Beesly',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        new CreatePatron(
            user: $user,
            organization: $organization,
            userId: null,
            patronTypeId: $patronType->id,
            homeBranchId: null,
            cardNumber: 'P-123',
            firstName: 'Pam',
            lastName: 'Beesly',
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
            permissions: [PermissionEnum::PatronCreate->value],
        );
        $otherPatronType = PatronType::factory()->create();

        new CreatePatron(
            user: $user,
            organization: $organization,
            userId: null,
            patronTypeId: $otherPatronType->id,
            homeBranchId: null,
            cardNumber: 'P-123',
            firstName: 'Pam',
            lastName: 'Beesly',
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
            permissions: [PermissionEnum::PatronCreate->value],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $otherBranch = Branch::factory()->create(['country_id' => null]);

        new CreatePatron(
            user: $user,
            organization: $organization,
            userId: null,
            patronTypeId: $patronType->id,
            homeBranchId: $otherBranch->id,
            cardNumber: 'P-123',
            firstName: 'Pam',
            lastName: 'Beesly',
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
            permissions: [PermissionEnum::PatronCreate->value],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        new CreatePatron(
            user: $user,
            organization: $organization,
            userId: 999999,
            patronTypeId: $patronType->id,
            homeBranchId: null,
            cardNumber: 'P-123',
            firstName: 'Pam',
            lastName: 'Beesly',
        )->execute();
    }
}
