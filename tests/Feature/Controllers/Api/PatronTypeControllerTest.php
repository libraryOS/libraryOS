<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Api;

use App\Enums\PermissionEnum;
use App\Models\Organization;
use App\Models\PatronType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PatronTypeControllerTest extends TestCase
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
                'is_active',
                'membership_duration_days',
                'max_loans',
                'keep_loan_history',
                'can_receive_notifications',
                'minimum_age',
                'maximum_age',
                'created_at',
                'updated_at',
            ],
            'links' => [
                'self',
            ],
        ],
    ];

    #[Test]
    public function it_lists_patron_types_for_an_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );

        PatronType::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Adult',
        ]);
        PatronType::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Child',
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/patron-types');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => $this->jsonStructure['data'],
            ],
        ]);
        $response->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_returns_empty_collection_when_no_patron_types_exist(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/patron-types');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    #[Test]
    public function it_restricts_listing_patron_types_to_organization_members(): void
    {
        $user = $this->createUser();
        $organization = Organization::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/patron-types');

        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_show_a_patron_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/patron-types/'.$patronType->id);

        $response->assertStatus(200);
        $response->assertJsonStructure($this->jsonStructure);
    }

    #[Test]
    public function it_returns_404_when_showing_a_patron_type_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $otherPatronType = PatronType::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/patron-types/'.$otherPatronType->id);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_create_a_patron_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/patron-types', [
            'key' => 'adult',
            'name' => 'Adult',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure($this->jsonStructure);
    }

    #[Test]
    public function it_requires_key_when_creating_a_patron_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/patron-types', [
            'name' => 'Adult',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_requires_name_when_creating_a_patron_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/patron-types', [
            'key' => 'adult',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_returns_404_when_a_user_doesnt_have_permission_to_create_a_patron_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/patron-types', [
            'key' => 'adult',
            'name' => 'Adult',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_update_a_patron_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/patron-types/'.$patronType->id, [
            'key' => 'adult-updated',
            'name' => 'Adult Updated',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure($this->jsonStructure);
    }

    #[Test]
    public function it_requires_key_when_updating_a_patron_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/patron-types/'.$patronType->id, [
            'name' => 'Adult Updated',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_requires_name_when_updating_a_patron_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/patron-types/'.$patronType->id, [
            'key' => 'adult-updated',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_returns_404_when_a_user_doesnt_have_permission_to_update_a_patron_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/patron-types/'.$patronType->id, [
            'key' => 'adult-updated',
            'name' => 'Adult Updated',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_destroy_a_patron_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('DELETE', '/api/organizations/'.$organization->id.'/adminland/patron-types/'.$patronType->id);

        $response->assertNoContent();
    }

    #[Test]
    public function it_returns_404_when_a_user_doesnt_have_permission_to_destroy_a_patron_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('DELETE', '/api/organizations/'.$organization->id.'/adminland/patron-types/'.$patronType->id);

        $response->assertStatus(404);
    }
}
