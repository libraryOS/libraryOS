<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\DestroyPatronType;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\PatronType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DestroyPatronTypeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_a_patron_type(): void
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
            'name' => 'Adult',
        ]);

        new DestroyPatronType(
            user: $user,
            organization: $organization,
            patronType: $patronType,
        )->execute();

        $this->assertDatabaseMissing('patron_types', [
            'id' => $patronType->id,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::PatronTypeDeletion
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Deleted a patron type called Adult'
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

        new DestroyPatronType(
            user: $user,
            organization: $organization,
            patronType: $patronType,
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

        new DestroyPatronType(
            user: $user,
            organization: $organization,
            patronType: $patronType,
        )->execute();
    }
}
