<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\App\Organization\Adminland;

use App\Enums\PermissionEnum;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BranchControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_the_branches_index_page(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value],
        );

        $country = Country::factory()->create([
            'name' => 'United States',
        ]);

        Branch::factory()->create([
            'organization_id' => $organization->id,
            'country_id' => $country->id,
            'name' => 'Main Office',
            'address_line_1' => '1725 Slough Avenue',
            'city' => 'Scranton',
        ]);

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/branches');

        $response->assertStatus(200);
        $response->assertViewHas(
            'branches',
            fn ($branchs): bool => $branchs->count() === 1
                && $branchs->first()->name === 'Main Office'
                && str_contains((string) $branchs->first()->address, '1725 Slough Avenue'),
        );
        $response->assertViewHas(
            'countries',
            fn ($countries): bool => $countries->contains(
                fn ($entry): bool => $entry->name === 'United States',
            ),
        );
    }

    #[Test]
    public function it_shows_the_create_branch_page(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value],
        );

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/branches/create');

        $response->assertStatus(200);
        $response->assertViewIs('app.organization.adminland.branches._create_branch');
    }

    #[Test]
    public function it_creates_a_branch(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value],
        );
        $country = Country::factory()->create();

        $response = $this->actingAs($user)
            ->post('/organizations/'.$organization->slug.'/adminland/branches', [
                'name' => 'Main Office',
                'address_line_1' => '1725 Slough Avenue',
                'address_line_2' => 'Floor 2',
                'city' => 'Scranton',
                'state_province' => 'PA',
                'postal_code' => '18505',
                'country_id' => $country->id,
                'timezone' => 'America/New_York',
            ]);

        $response->assertRedirect(route('organization.adminland.branch.index', $organization->slug));
        $response->assertSessionHas('status', 'Changes saved');

        $this->assertDatabaseHas('branches', [
            'organization_id' => $organization->id,
            'country_id' => $country->id,
            'name' => 'Main Office',
            'address_line_1' => '1725 Slough Avenue',
            'address_line_2' => 'Floor 2',
            'city' => 'Scranton',
            'state_province' => 'PA',
            'postal_code' => '18505',
            'timezone' => 'America/New_York',
        ]);
    }

    #[Test]
    public function it_shows_the_edit_branch_page(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/branches/'.$branch->id);

        $response->assertStatus(200);
        $response->assertViewIs('app.organization.adminland.branches._edit_branch');
        $response->assertViewHas('branch', $branch);
    }

    #[Test]
    public function it_returns_404_when_editing_a_branch_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value],
        );

        $otherOrganization = Organization::factory()->create();
        $branch = Branch::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/branches/'.$branch->id);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_updates_a_branch(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value],
        );
        $country = Country::factory()->create();
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Old Office',
        ]);

        $response = $this->actingAs($user)
            ->put('/organizations/'.$organization->slug.'/adminland/branches/'.$branch->id, [
                'name' => 'Updated Office',
                'address_line_1' => '12 Main Street',
                'address_line_2' => null,
                'city' => 'Paris',
                'state_province' => null,
                'postal_code' => '75001',
                'country_id' => $country->id,
                'timezone' => 'Europe/Paris',
            ]);

        $response->assertRedirect(route('organization.adminland.branch.index', $organization->slug));
        $response->assertSessionHas('status', 'Changes saved');

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Updated Office',
            'address_line_1' => '12 Main Street',
            'city' => 'Paris',
            'postal_code' => '75001',
            'country_id' => $country->id,
            'timezone' => 'Europe/Paris',
        ]);
    }

    #[Test]
    public function it_returns_404_when_updating_a_branch_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value],
        );

        $otherOrganization = Organization::factory()->create();
        $branch = Branch::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->actingAs($user)
            ->put('/organizations/'.$organization->slug.'/adminland/branches/'.$branch->id, [
                'name' => 'Updated Office',
                'address_line_1' => '12 Main Street',
                'city' => 'Paris',
            ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_deletes_a_branch(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value],
        );
        $branch = Branch::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $response = $this->actingAs($user)
            ->delete('/organizations/'.$organization->slug.'/adminland/branches/'.$branch->id);

        $response->assertRedirect(route('organization.adminland.branch.index', $organization->slug));
        $response->assertSessionHas('status', 'Changes saved');

        $this->assertDatabaseMissing('branches', [
            'id' => $branch->id,
        ]);
    }

    #[Test]
    public function it_returns_404_when_deleting_a_branch_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::BranchManage->value],
        );

        $otherOrganization = $this->createOrganization();
        $branch = Branch::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->actingAs($user)
            ->delete('/organizations/'.$organization->slug.'/adminland/branches/'.$branch->id);

        $response->assertStatus(404);
    }
}
