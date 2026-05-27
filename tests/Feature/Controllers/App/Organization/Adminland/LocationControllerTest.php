<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\App\Organization\Adminland;

use App\Enums\PermissionEnum;
use App\Models\Branch;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    #[Test]
    public function it_shows_the_locations_index_page(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );

        $branch = Branch::factory()->create(['organization_id' => $organization->id, 'name' => 'Main Branch']);
        Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'name' => 'Fiction Section',
        ]);

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/locations');

        $response->assertStatus(200);
        $response->assertViewIs('app.organization.adminland.locations.index');
        $response->assertViewHas('hasBranches', true);
        $response->assertViewHas(
            'locationsByBranch',
            fn ($branches): bool => $branches->first()->name === 'Main Branch'
                && $branches->first()->locations->first()->name === 'Fiction Section',
        );
    }

    #[Test]
    public function it_shows_no_branches_state_on_index(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/locations');

        $response->assertStatus(200);
        $response->assertViewHas('hasBranches', false);
    }

    #[Test]
    public function it_denies_access_to_index_without_permission(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/locations');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    #[Test]
    public function it_shows_the_create_location_page(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );
        Branch::factory()->create(['organization_id' => $organization->id, 'name' => 'Main Branch']);

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/locations/create');

        $response->assertStatus(200);
        $response->assertViewIs('app.organization.adminland.locations.create');
        $response->assertViewHas(
            'branchOptions',
            fn ($options): bool => in_array('Main Branch', $options, true),
        );
    }

    #[Test]
    public function it_redirects_to_index_when_no_branches_exist_on_create(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/locations/create');

        $response->assertRedirect(route('organization.adminland.location.index', $organization->slug));
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    #[Test]
    public function it_creates_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);

        $response = $this->actingAs($user)
            ->post('/organizations/'.$organization->slug.'/adminland/locations', [
                'branch_id' => $branch->id,
                'name' => 'Fiction Section',
                'code' => 'FIC',
                'description' => 'All fiction books.',
                'is_active' => '1',
                'is_public' => '1',
                'supports_pickups' => '0',
                'supports_returns' => '0',
            ]);

        $response->assertRedirect(route('organization.adminland.location.index', $organization->slug));
        $response->assertSessionHas('status', 'Changes saved');

        $this->assertDatabaseHas('locations', [
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'name' => 'Fiction Section',
            'code' => 'FIC',
            'description' => 'All fiction books.',
            'is_active' => true,
            'is_public' => true,
            'supports_pickups' => false,
            'supports_returns' => false,
            'parent_id' => null,
        ]);
    }

    #[Test]
    public function it_creates_a_child_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $parent = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'name' => 'Fiction Section',
        ]);

        $response = $this->actingAs($user)
            ->post('/organizations/'.$organization->slug.'/adminland/locations', [
                'branch_id' => $branch->id,
                'parent_id' => $parent->id,
                'name' => 'Shelf B3',
            ]);

        $response->assertRedirect(route('organization.adminland.location.index', $organization->slug));

        $this->assertDatabaseHas('locations', [
            'organization_id' => $organization->id,
            'parent_id' => $parent->id,
            'name' => 'Shelf B3',
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_create(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );

        $response = $this->actingAs($user)
            ->post('/organizations/'.$organization->slug.'/adminland/locations', []);

        $response->assertSessionHasErrors(['branch_id', 'name']);
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    #[Test]
    public function it_shows_the_edit_location_page(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'name' => 'Fiction Section',
        ]);

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/locations/'.$location->id);

        $response->assertStatus(200);
        $response->assertViewIs('app.organization.adminland.locations.edit');
        $response->assertViewHas('location', $location);
    }

    #[Test]
    public function it_returns_404_when_editing_a_location_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );

        $otherOrganization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $otherOrganization->id]);
        $location = Location::factory()->create([
            'organization_id' => $otherOrganization->id,
            'branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/locations/'.$location->id);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    #[Test]
    public function it_updates_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'name' => 'Old Name',
        ]);

        $response = $this->actingAs($user)
            ->put('/organizations/'.$organization->slug.'/adminland/locations/'.$location->id, [
                'branch_id' => $branch->id,
                'name' => 'Updated Name',
                'code' => 'UPD',
                'description' => 'Updated description.',
                'is_active' => '1',
                'is_public' => '0',
                'supports_pickups' => '1',
                'supports_returns' => '1',
            ]);

        $response->assertRedirect(route('organization.adminland.location.index', $organization->slug));
        $response->assertSessionHas('status', 'Changes saved');

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Updated Name',
            'code' => 'UPD',
            'description' => 'Updated description.',
            'is_active' => true,
            'is_public' => false,
            'supports_pickups' => true,
            'supports_returns' => true,
        ]);
    }

    #[Test]
    public function it_returns_404_when_updating_a_location_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );

        $otherOrganization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $otherOrganization->id]);
        $location = Location::factory()->create([
            'organization_id' => $otherOrganization->id,
            'branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($user)
            ->put('/organizations/'.$organization->slug.'/adminland/locations/'.$location->id, [
                'branch_id' => $branch->id,
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    #[Test]
    public function it_deletes_a_location(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $location = Location::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($user)
            ->delete('/organizations/'.$organization->slug.'/adminland/locations/'.$location->id);

        $response->assertRedirect(route('organization.adminland.location.index', $organization->slug));
        $response->assertSessionHas('status', 'Changes saved');

        $this->assertDatabaseMissing('locations', [
            'id' => $location->id,
        ]);
    }

    #[Test]
    public function it_returns_404_when_deleting_a_location_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::LocationManage->value],
        );

        $otherOrganization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $otherOrganization->id]);
        $location = Location::factory()->create([
            'organization_id' => $otherOrganization->id,
            'branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($user)
            ->delete('/organizations/'.$organization->slug.'/adminland/locations/'.$location->id);

        $response->assertStatus(404);
    }
}
