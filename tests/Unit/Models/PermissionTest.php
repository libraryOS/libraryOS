<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_an_organization(): void
    {
        $permission = Permission::factory()->create();

        $this->assertTrue($permission->organization()->exists());
    }
}
