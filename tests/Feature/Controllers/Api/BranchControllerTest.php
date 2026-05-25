<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Api;

use App\Enums\PermissionEnum;
use App\Models\Branch;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BranchControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $jsonStructure = [
        'data' => [
            'type',
            'id',
            'attributes' => [
                'name',
                'slug',
                'code',
                'description',
                'address_line_1',
                'address_line_2',
                'city',
                'state_province',
                'postal_code',
                'timezone',
                'country_id',
                'created_at',
                'updated_at',
            ],
            'links' => [
                'self',
            ],
        ],
    ];

    #[Test]
    public function it_lists_branches_for_an_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );

        Branch::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Alpha Office',
        ]);
        Branch::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Beta Office',
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/branches');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => $this->jsonStructure['data'],
            ],
        ]);
        $response->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_restricts_listing_branches_to_organization_members(): void
    {
        $user = $this->createUser();
        $organization = Organization::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/branches');

        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_show_a_branch(): void
    {
        $user = $this->createUser();
        $organization = Organization::factory()->create();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/branches/'.$branch->id);

        $response->assertStatus(200);
        $response->assertJsonStructure($this->jsonStructure);
    }

    #[Test]
    public function it_returns_404_when_showing_a_branch_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = Organization::factory()->create();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $otherOffice = Branch::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/organizations/'.$organization->id.'/adminland/branches/'.$otherOffice->id);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_create_a_branch(): void
    {
        $user = $this->createUser();
        $organization = Organization::factory()->create();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value],
        );

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/branches', [
            'name' => 'Scranton Branch',
            'address_line_1' => '1725 Slough Avenue',
            'city' => 'Scranton',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure($this->jsonStructure);
    }

    #[Test]
    public function it_cant_create_a_branch_if_the_user_does_not_have_permission(): void
    {
        $user = $this->createUser();
        $organization = Organization::factory()->create();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/organizations/'.$organization->id.'/adminland/branches', [
            'name' => 'Scranton Branch',
            'address_line_1' => '1725 Slough Avenue',
            'city' => 'Scranton',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_update_a_branch(): void
    {
        $user = $this->createUser();
        $organization = Organization::factory()->create();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value],
        );
        $office = Branch::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/branches/'.$office->id, [
            'name' => 'Scranton Branch Updated',
            'address_line_1' => '1725 Slough Avenue',
            'city' => 'Scranton',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure($this->jsonStructure);
    }

    #[Test]
    public function it_cant_update_a_branch_if_the_user_does_not_have_permission(): void
    {
        $user = $this->createUser();
        $organization = Organization::factory()->create();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $office = Branch::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('PUT', '/api/organizations/'.$organization->id.'/adminland/branches/'.$office->id, [
            'name' => 'Scranton Branch',
            'address_line_1' => '1725 Slough Avenue',
            'city' => 'Scranton',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_destroy_a_branch(): void
    {
        $user = $this->createUser();
        $organization = Organization::factory()->create();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value],
        );
        $office = Branch::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('DELETE', '/api/organizations/'.$organization->id.'/adminland/branches/'.$office->id);

        $response->assertNoContent();
    }

    #[Test]
    public function it_cant_destroy_a_branch_if_the_user_does_not_have_permission(): void
    {
        $user = $this->createUser();
        $organization = Organization::factory()->create();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );
        $office = Branch::factory()->create([
            'organization_id' => $organization->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->json('DELETE', '/api/organizations/'.$organization->id.'/adminland/branches/'.$office->id);

        $response->assertStatus(404);
    }
}
