<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Api;

use App\Enums\PermissionEnum;
use App\Models\ItemType;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ItemTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $jsonStructure = [
        'data' => [
            'type',
            'id',
            'attributes' => [
                'key',
                'name',
                'description',
                'is_loanable',
                'is_holdable',
                'is_visible_in_catalog',
                'default_loan_days',
                'created_at',
                'updated_at',
            ],
            'links' => [
                'self',
            ],
        ],
    ];

    #[Test]
    public function it_lists_item_types_for_an_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );

        ItemType::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Alpha Type',
        ]);
        ItemType::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Beta Type',
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/item-types');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => $this->jsonStructure['data'],
            ],
        ]);
        $response->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_returns_empty_collection_when_no_item_types_exist(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/item-types');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    #[Test]
    public function it_restricts_listing_item_types_to_organization_members(): void
    {
        $user = $this->createUser();
        $organization = Organization::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/item-types');

        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_show_an_item_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $itemType = ItemType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/item-types/'.$itemType->id);

        $response->assertStatus(200);
        $response->assertJsonStructure($this->jsonStructure);
    }

    #[Test]
    public function it_returns_404_when_showing_an_item_type_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $otherItemType = ItemType::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/item-types/'.$otherItemType->id);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_create_an_item_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value],
        );

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/item-types', [
            'key' => 'book',
            'name' => 'Book',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure($this->jsonStructure);
    }

    #[Test]
    public function it_requires_key_when_creating_an_item_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::ItemTypeManage->value],
        );

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/item-types', [
            'name' => 'Book',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_returns_404_when_a_user_doesnt_have_permission_to_create_an_item_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/item-types', [
            'key' => 'book',
            'name' => 'Book',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_update_an_item_type(): void
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

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/item-types/'.$itemType->id, [
            'key' => 'book-updated',
            'name' => 'Book Updated',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure($this->jsonStructure);
    }

    #[Test]
    public function it_requires_key_when_updating_an_item_type(): void
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

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/item-types/'.$itemType->id, [
            'name' => 'Book Updated',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_returns_404_when_a_user_doesnt_have_permission_to_update_an_item_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $itemType = ItemType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/item-types/'.$itemType->id, [
            'key' => 'book-updated',
            'name' => 'Book Updated',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_destroy_an_item_type(): void
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

        Sanctum::actingAs($user);

        $response = $this->json('DELETE', '/api/organizations/'.$organization->id.'/adminland/item-types/'.$itemType->id);

        $response->assertNoContent();
    }

    #[Test]
    public function it_returns_404_when_a_user_doesnt_have_permission_to_destroy_an_item_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $itemType = ItemType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('DELETE', '/api/organizations/'.$organization->id.'/adminland/item-types/'.$itemType->id);

        $response->assertStatus(404);
    }
}
