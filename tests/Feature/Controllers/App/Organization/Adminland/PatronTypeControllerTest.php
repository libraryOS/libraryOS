<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\App\Organization\Adminland;

use App\Enums\PermissionEnum;
use App\Models\Organization;
use App\Models\PatronType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PatronTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_the_patron_types_index_page(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );

        PatronType::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Adult',
            'key' => 'adult',
        ]);

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/patron-types');

        $response->assertStatus(200);
        $response->assertViewHas(
            'patronTypes',
            fn ($patronTypes): bool => $patronTypes->count() === 1
                && $patronTypes->first()->name === 'Adult'
                && $patronTypes->first()->key === 'adult',
        );
    }

    #[Test]
    public function it_shows_the_create_patron_type_page(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/patron-types/create');

        $response->assertStatus(200);
        $response->assertViewIs('app.organization.adminland.patron-types.create');
    }

    #[Test]
    public function it_creates_a_patron_type(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );

        $response = $this->actingAs($user)
            ->post('/organizations/'.$organization->slug.'/adminland/patron-types', [
                'key' => 'adult',
                'name' => 'Adult',
                'description' => 'Standard adult membership',
                'is_active' => '1',
                'membership_duration_days' => '365',
                'max_loans' => '5',
                'keep_loan_history' => '0',
                'can_receive_notifications' => '1',
                'minimum_age' => '18',
                'maximum_age' => null,
            ]);

        $response->assertRedirect(route('organization.adminland.patron-type.index', $organization->slug));
        $response->assertSessionHas('status', 'Changes saved');

        $this->assertDatabaseHas('patron_types', [
            'organization_id' => $organization->id,
            'key' => 'adult',
            'name' => 'Adult',
            'description' => 'Standard adult membership',
            'is_active' => true,
            'membership_duration_days' => 365,
            'max_loans' => 5,
            'keep_loan_history' => false,
            'can_receive_notifications' => true,
            'minimum_age' => 18,
            'maximum_age' => null,
        ]);
    }

    #[Test]
    public function it_shows_the_edit_patron_type_page(): void
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

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/patron-types/'.$patronType->id);

        $response->assertStatus(200);
        $response->assertViewIs('app.organization.adminland.patron-types.edit');
        $response->assertViewHas('patronType', $patronType);
    }

    #[Test]
    public function it_returns_404_when_editing_a_patron_type_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );

        $otherOrganization = Organization::factory()->create();
        $patronType = PatronType::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/adminland/patron-types/'.$patronType->id);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_updates_a_patron_type(): void
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
            'name' => 'Old Name',
            'key' => 'old-key',
        ]);

        $response = $this->actingAs($user)
            ->put('/organizations/'.$organization->slug.'/adminland/patron-types/'.$patronType->id, [
                'key' => 'senior',
                'name' => 'Senior',
                'description' => null,
                'is_active' => '1',
                'membership_duration_days' => '365',
                'max_loans' => '3',
                'keep_loan_history' => '1',
                'can_receive_notifications' => '0',
                'minimum_age' => '65',
                'maximum_age' => null,
            ]);

        $response->assertRedirect(route('organization.adminland.patron-type.index', $organization->slug));
        $response->assertSessionHas('status', 'Changes saved');

        $this->assertDatabaseHas('patron_types', [
            'id' => $patronType->id,
            'key' => 'senior',
            'name' => 'Senior',
            'is_active' => true,
            'membership_duration_days' => 365,
            'max_loans' => 3,
            'keep_loan_history' => true,
            'can_receive_notifications' => false,
            'minimum_age' => 65,
            'maximum_age' => null,
        ]);
    }

    #[Test]
    public function it_returns_404_when_updating_a_patron_type_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );

        $otherOrganization = Organization::factory()->create();
        $patronType = PatronType::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->actingAs($user)
            ->put('/organizations/'.$organization->slug.'/adminland/patron-types/'.$patronType->id, [
                'key' => 'senior',
                'name' => 'Senior',
            ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function it_deletes_a_patron_type(): void
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

        $response = $this->actingAs($user)
            ->delete('/organizations/'.$organization->slug.'/adminland/patron-types/'.$patronType->id);

        $response->assertRedirect(route('organization.adminland.patron-type.index', $organization->slug));
        $response->assertSessionHas('status', 'Changes saved');

        $this->assertDatabaseMissing('patron_types', [
            'id' => $patronType->id,
        ]);
    }

    #[Test]
    public function it_returns_404_when_deleting_a_patron_type_from_another_organization(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::PatronTypeManage->value],
        );

        $otherOrganization = $this->createOrganization();
        $patronType = PatronType::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->actingAs($user)
            ->delete('/organizations/'.$organization->slug.'/adminland/patron-types/'.$patronType->id);

        $response->assertStatus(404);
    }
}
