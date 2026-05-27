<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Api;

use App\Enums\PermissionEnum;
use App\Models\Branch;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $jsonStructure = [
        'data' => [
            'type',
            'id',
            'attributes' => [
                'organization_id',
                'branch_id',
                'parent_id',
                'name',
                'code',
                'description',
                'is_active',
                'is_public',
                'supports_pickups',
                'supports_returns',
                'created_at',
                'updated_at',
            ],
            'links' => [
                'self',
            ],
        ],
    ];

    #[Test]
    public function it_lists_locations_for_an_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);

        Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'name' => 'Main Stacks',
        ]);
        Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'name' => 'Reference Room',
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/locations');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => $this->jsonStructure['data'],
            ],
        ]);
        $response->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_returns_empty_collection_when_no_locations_exist(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/locations');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    #[Test]
    public function it_restricts_listing_locations_to_organization_members(): void
    {
        $user = $this->createUser();
        $organization = Organization::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/locations');

        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_show_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/locations/'.$location->id);

        $response->assertStatus(200);
        $response->assertJsonStructure($this->jsonStructure);
    }

    #[Test]
    public function it_returns_404_when_showing_a_location_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $otherLocation = Location::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/locations/'.$otherLocation->id);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_create_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/locations', [
            'branch_id' => $branch->id,
            'name' => 'Main Stacks',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure($this->jsonStructure);
    }

    #[Test]
    public function it_requires_branch_id_when_creating_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/locations', [
            'name' => 'Main Stacks',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_requires_name_when_creating_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/locations', [
            'branch_id' => $branch->id,
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_returns_404_when_a_user_doesnt_have_permission_to_create_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/locations', [
            'branch_id' => $branch->id,
            'name' => 'Main Stacks',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_update_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/locations/'.$location->id, [
            'branch_id' => $branch->id,
            'name' => 'Updated Stacks',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure($this->jsonStructure);
    }

    #[Test]
    public function it_requires_branch_id_when_updating_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/locations/'.$location->id, [
            'name' => 'Updated Stacks',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_requires_name_when_updating_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/locations/'.$location->id, [
            'branch_id' => $branch->id,
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_returns_404_when_a_user_doesnt_have_permission_to_update_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/locations/'.$location->id, [
            'branch_id' => $branch->id,
            'name' => 'Updated Stacks',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_destroy_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('DELETE', '/api/organizations/'.$organization->id.'/adminland/locations/'.$location->id);

        $response->assertNoContent();
    }

    #[Test]
    public function it_returns_404_when_a_user_doesnt_have_permission_to_destroy_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => null,
        ]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('DELETE', '/api/organizations/'.$organization->id.'/adminland/locations/'.$location->id);

        $response->assertStatus(404);
    }
}
