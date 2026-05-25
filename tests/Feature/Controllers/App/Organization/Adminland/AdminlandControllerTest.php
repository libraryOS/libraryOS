<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\App\Organization\Adminland;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminlandControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_the_adminland(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [PermissionEnum::AdminlandAccess->value],
        );

        $response = $this->actingAs($user)->get('/organizations/'.$organization->slug.'/adminland');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_restricts_adminland(): void
    {
        $user = $this->createUser();
        $organization = $this->createOrganization();
        $this->assignUserToOrganization(
            user: $user,
            organization: $organization,
            permissions: [],
        );

        $response = $this->actingAs($user)->get('/organizations/'.$organization->slug.'/adminland');

        $response->assertStatus(403);
    }
}
