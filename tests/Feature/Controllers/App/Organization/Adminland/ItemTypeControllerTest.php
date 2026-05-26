<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\App\Organization\Adminland;

use App\Enums\PermissionEnum;
use App\Models\ItemType;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ItemTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_the_item_types_index_page(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value],
        );

        ItemType::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Book',
            'key' => 'book',
        ]);

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/item-types');

        $response->assertStatus(200);
        $response->assertViewHas(
            'itemTypes',
            fn ($itemTypes): bool => $itemTypes->count() === 1
                && $itemTypes->first()->name === 'Book'
                && $itemTypes->first()->key === 'book',
        );
    }

    #[Test]
    public function it_shows_the_create_item_type_page(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value],
        );

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/item-types/create');

        $response->assertStatus(200);
        $response->assertViewIs('app.organization.adminland.item-types.create');
    }

    #[Test]
    public function it_creates_an_item_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value],
        );

        $response = $this->actingAs($user)
            ->post('/organizations/'.$organization->slug.'/adminland/item-types', [
                'key' => 'dvd',
                'name' => 'DVD',
                'description' => 'A DVD disc',
                'is_loanable' => '1',
                'is_holdable' => '1',
                'is_visible_in_catalog' => '0',
                'default_loan_days' => '7',
            ]);

        $response->assertRedirect(route('organization.adminland.item-type.index', $organization->slug));
        $response->assertSessionHas('status', 'Changes saved');

        $this->assertDatabaseHas('item_types', [
            'organization_id' => $organization->id,
            'key' => 'dvd',
            'name' => 'DVD',
            'description' => 'A DVD disc',
            'is_loanable' => true,
            'is_holdable' => true,
            'is_visible_in_catalog' => false,
            'default_loan_days' => 7,
        ]);
    }

    #[Test]
    public function it_shows_the_edit_item_type_page(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value],
        );
        $itemType = ItemType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/item-types/'.$itemType->id);

        $response->assertStatus(200);
        $response->assertViewIs('app.organization.adminland.item-types.edit');
        $response->assertViewHas('itemType', $itemType);
    }

    #[Test]
    public function it_returns_404_when_editing_an_item_type_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value],
        );

        $otherOrganization = Organization::factory()->create();
        $itemType = ItemType::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/item-types/'.$itemType->id);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_updates_an_item_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value],
        );
        $itemType = ItemType::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Old Name',
            'key' => 'old-key',
        ]);

        $response = $this->actingAs($user)
            ->put('/organizations/'.$organization->slug.'/adminland/item-types/'.$itemType->id, [
                'key' => 'magazine',
                'name' => 'Magazine',
                'description' => null,
                'is_loanable' => '1',
                'is_holdable' => '0',
                'is_visible_in_catalog' => '1',
                'default_loan_days' => '14',
            ]);

        $response->assertRedirect(route('organization.adminland.item-type.index', $organization->slug));
        $response->assertSessionHas('status', 'Changes saved');

        $this->assertDatabaseHas('item_types', [
            'id' => $itemType->id,
            'key' => 'magazine',
            'name' => 'Magazine',
            'is_loanable' => true,
            'is_holdable' => false,
            'is_visible_in_catalog' => true,
            'default_loan_days' => 14,
        ]);
    }

    #[Test]
    public function it_returns_404_when_updating_an_item_type_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value],
        );

        $otherOrganization = Organization::factory()->create();
        $itemType = ItemType::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->actingAs($user)
            ->put('/organizations/'.$organization->slug.'/adminland/item-types/'.$itemType->id, [
                'key' => 'magazine',
                'name' => 'Magazine',
            ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_deletes_an_item_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value],
        );
        $itemType = ItemType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $response = $this->actingAs($user)
            ->delete('/organizations/'.$organization->slug.'/adminland/item-types/'.$itemType->id);

        $response->assertRedirect(route('organization.adminland.item-type.index', $organization->slug));
        $response->assertSessionHas('status', 'Changes saved');

        $this->assertDatabaseMissing('item_types', [
            'id' => $itemType->id,
        ]);
    }

    #[Test]
    public function it_returns_404_when_deleting_an_item_type_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value],
        );

        $otherOrganization = $this->createOrganization();
        $itemType = ItemType::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->actingAs($user)
            ->delete('/organizations/'.$organization->slug.'/adminland/item-types/'.$itemType->id);

        $response->assertStatus(404);
    }
}
