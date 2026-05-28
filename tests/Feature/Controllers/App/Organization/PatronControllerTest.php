<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\App\Organization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PatronControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_the_patron_profile_mock_page(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );

        $response = $this->actingAs($user)
            ->get('/organizations/'.$organization->slug.'/patrons/P-1001');

        $response->assertStatus(200);
        $response->assertViewIs('app.organization.patrons.show');
        $response->assertSee('Amelia Lopez');
        $response->assertSee('Activity');
        $response->assertSee('Loans');
    }
}
