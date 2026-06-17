<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\UpdateEdition;
use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Edition;
use App\Models\ItemType;
use App\Models\Work;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateEditionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_an_edition(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization($user, $organization, [PermissionEnum::EditionManage->value]);
        $edition = Edition::factory()->create(['organization_id' => $organization->id]);
        $work = Work::factory()->create(['organization_id' => $organization->id]);
        $itemType = ItemType::factory()->create(['organization_id' => $organization->id]);

        $updated = new UpdateEdition(
            user: $user,
            organization: $organization,
            edition: $edition,
            work: $work,
            itemType: $itemType,
            title: 'The Fellowship of the Ring',
            isbn: '9780261102354',
            publisher: 'Allen & Unwin',
            publicationYear: 1954,
            language: 'en',
            coverImagePath: 'covers/fellowship.jpg',
        )->execute();

        $this->assertSame('The Fellowship of the Ring', $updated->title);
        $this->assertDatabaseHas('editions', [
            'id' => $edition->id,
            'work_id' => $work->id,
            'item_type_id' => $itemType->id,
            'title' => 'The Fellowship of the Ring',
            'isbn' => '9780261102354',
            'publisher' => 'Allen &amp; Unwin',
            'publication_year' => 1954,
            'language' => 'en',
            'cover_image_path' => 'covers/fellowship.jpg',
        ]);
        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::EditionUpdate
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Updated an edition called The Fellowship of the Ring'
            ),
        );
    }

    #[Test]
    public function it_throws_an_exception_if_user_does_not_have_the_right_permission(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization($user, $organization, ['random.permission']);
        $edition = Edition::factory()->create(['organization_id' => $organization->id]);
        $work = Work::factory()->create(['organization_id' => $organization->id]);
        $itemType = ItemType::factory()->create(['organization_id' => $organization->id]);

        new UpdateEdition($user, $organization, $edition, $work, $itemType, 'The Hobbit')->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_edition_does_not_belong_to_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization($user, $organization, [PermissionEnum::EditionManage->value]);
        $edition = Edition::factory()->create(['organization_id' => $this->createOrganization()->id]);
        $work = Work::factory()->create(['organization_id' => $organization->id]);
        $itemType = ItemType::factory()->create(['organization_id' => $organization->id]);

        new UpdateEdition($user, $organization, $edition, $work, $itemType, 'The Hobbit')->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_work_does_not_belong_to_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization($user, $organization, [PermissionEnum::EditionManage->value]);
        $edition = Edition::factory()->create(['organization_id' => $organization->id]);
        $work = Work::factory()->create(['organization_id' => $this->createOrganization()->id]);
        $itemType = ItemType::factory()->create(['organization_id' => $organization->id]);

        new UpdateEdition($user, $organization, $edition, $work, $itemType, 'The Hobbit')->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_item_type_does_not_belong_to_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization($user, $organization, [PermissionEnum::EditionManage->value]);
        $edition = Edition::factory()->create(['organization_id' => $organization->id]);
        $work = Work::factory()->create(['organization_id' => $organization->id]);
        $itemType = ItemType::factory()->create(['organization_id' => $this->createOrganization()->id]);

        new UpdateEdition($user, $organization, $edition, $work, $itemType, 'The Hobbit')->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $edition = Edition::factory()->create(['organization_id' => $organization->id]);
        $work = Work::factory()->create(['organization_id' => $organization->id]);
        $itemType = ItemType::factory()->create(['organization_id' => $organization->id]);

        new UpdateEdition($user, $organization, $edition, $work, $itemType, 'The Hobbit')->execute();
    }
}
