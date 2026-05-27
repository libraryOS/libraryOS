<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\DestroyItemType;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\ItemType;
use App\Models\Organization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DestroyItemTypeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_an_item_type(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value]
        );
        $itemType = ItemType::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Book',
        ]);

        new DestroyItemType(
            user: $user,
            organization: $organization,
            itemType: $itemType,
        )->execute();

        $this->assertDatabaseMissing('item_types', [
            'id' => $itemType->id,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::ItemTypeDeletion
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Deleted an item type called Book'
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
        $itemType = ItemType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        new DestroyItemType(
            user: $user,
            organization: $organization,
            itemType: $itemType,
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_item_type_does_not_belong_to_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value]
        );
        $otherOrganization = $this->createOrganization();
        $itemType = ItemType::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        new DestroyItemType(
            user: $user,
            organization: $organization,
            itemType: $itemType,
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
            permissions: [PermissionEnum::ItemTypeManage->value]
        );
        $itemType = ItemType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        new DestroyItemType(
            user: $user,
            organization: $organization,
            itemType: $itemType,
        )->execute();
    }
}
