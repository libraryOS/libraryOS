<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreateItemType;
use App\Enums\PermissionEnum;
use App\Jobs\LogUserAction;
use App\Models\ItemType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateItemTypeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_an_item_type(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value]
        );

        $itemType = new CreateItemType(
            user: $user,
            organization: $organization,
            key: 'book',
            name: 'Book',
            nameTranslationKey: null,
            description: 'A physical book',
            isLoanable: true,
            isHoldable: true,
            isVisibleInCatalog: true,
            defaultLoanDays: 21,
        )->execute();

        $this->assertInstanceOf(ItemType::class, $itemType);

        $this->assertDatabaseHas('item_types', [
            'id' => $itemType->id,
            'organization_id' => $organization->id,
            'key' => 'book',
            'name' => 'Book',
            'name_translation_key' => null,
            'description' => 'A physical book',
            'is_loanable' => true,
            'is_holdable' => true,
            'is_visible_in_catalog' => true,
            'default_loan_days' => 21,
        ]);

        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === 'item_type_creation'
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Created an item type called Book'
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

        new CreateItemType(
            user: $user,
            organization: $organization,
            key: 'book',
            name: 'Book',
        )->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $otherOrganization = $this->createOrganization();

        new CreateItemType(
            user: $user,
            organization: $otherOrganization,
            key: 'book',
            name: 'Book',
        )->execute();
    }
}
