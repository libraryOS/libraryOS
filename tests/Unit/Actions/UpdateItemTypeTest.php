<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\UpdateItemType;
use App\Enums\PermissionEnum;
use App\Jobs\LogUserAction;
use App\Models\ItemType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateItemTypeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_an_item_type(): void
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
            'key' => 'old-key',
            'name' => 'Old Name',
        ]);

        $updated = new UpdateItemType(
            user: $user,
            organization: $organization,
            itemType: $itemType,
            key: 'dvd',
            name: 'DVD',
            description: 'A DVD disc',
            isLoanable: true,
            isHoldable: false,
            isVisibleInCatalog: true,
            defaultLoanDays: 7,
        )->execute();

        $this->assertSame('DVD', $updated->getName());

        $this->assertDatabaseHas('item_types', [
            'id' => $itemType->id,
            'key' => 'dvd',
            'name' => 'DVD',
            'description' => 'A DVD disc',
            'is_loanable' => true,
            'is_holdable' => false,
            'is_visible_in_catalog' => true,
            'default_loan_days' => 7,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === 'item_type_update'
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Updated an item type called DVD'
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

        new UpdateItemType(
            user: $user,
            organization: $organization,
            itemType: $itemType,
            key: 'dvd',
            name: 'DVD',
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

        new UpdateItemType(
            user: $user,
            organization: $organization,
            itemType: $itemType,
            key: 'dvd',
            name: 'DVD',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $itemType = ItemType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        new UpdateItemType(
            user: $user,
            organization: $organization,
            itemType: $itemType,
            key: 'dvd',
            name: 'DVD',
        )->execute();
    }
}
