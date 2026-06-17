<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CreateEdition;
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

class CreateEditionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_an_edition(): void
    {
        Queue::fake();

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization($user, $organization, [PermissionEnum::EditionManage->value]);
        $work = Work::factory()->create(['organization_id' => $organization->id]);
        $itemType = ItemType::factory()->create(['organization_id' => $organization->id]);

        $edition = new CreateEdition(
            user: $user,
            organization: $organization,
            work: $work,
            itemType: $itemType,
            title: 'The Hobbit',
            isbn: '9780261103344',
            publisher: 'HarperCollins',
            publicationYear: 1995,
            language: 'en',
            coverImagePath: 'covers/the-hobbit.jpg',
        )->execute();

        $this->assertInstanceOf(Edition::class, $edition);
        $this->assertDatabaseHas('editions', [
            'id' => $edition->id,
            'organization_id' => $organization->id,
            'work_id' => $work->id,
            'item_type_id' => $itemType->id,
            'title' => 'The Hobbit',
            'isbn' => '9780261103344',
            'publisher' => 'HarperCollins',
            'publication_year' => 1995,
            'language' => 'en',
            'cover_image_path' => 'covers/the-hobbit.jpg',
        ]);
        Queue::assertPushedOn(
            queue: 'low',
            job: LogUserAction::class,
            callback: fn (LogUserAction $job): bool => (
                $job->action === UserActionEnum::EditionCreation
                && $job->user->id === $user->id
                && $job->organization->id === $organization->id
                && $job->description === 'Created an edition called The Hobbit'
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
        $work = Work::factory()->create(['organization_id' => $organization->id]);
        $itemType = ItemType::factory()->create(['organization_id' => $organization->id]);

        new CreateEdition($user, $organization, $work, $itemType, 'The Hobbit')->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_user_is_not_part_of_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $work = Work::factory()->create(['organization_id' => $organization->id]);
        $itemType = ItemType::factory()->create(['organization_id' => $organization->id]);

        new CreateEdition($user, $organization, $work, $itemType, 'The Hobbit')->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_work_does_not_belong_to_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization($user, $organization, [PermissionEnum::EditionManage->value]);
        $work = Work::factory()->create(['organization_id' => $this->createOrganization()->id]);
        $itemType = ItemType::factory()->create(['organization_id' => $organization->id]);

        new CreateEdition($user, $organization, $work, $itemType, 'The Hobbit')->execute();
    }

    #[Test]
    public function it_throws_an_exception_if_item_type_does_not_belong_to_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization($user, $organization, [PermissionEnum::EditionManage->value]);
        $work = Work::factory()->create(['organization_id' => $organization->id]);
        $itemType = ItemType::factory()->create(['organization_id' => $this->createOrganization()->id]);

        new CreateEdition($user, $organization, $work, $itemType, 'The Hobbit')->execute();
    }
}
