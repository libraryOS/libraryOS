<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Organization;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_an_organization(): void
    {
        $organization = Organization::factory()->create();
        $work = Work::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $this->assertTrue($work->organization()->exists());
        $this->assertEquals($organization->id, $work->organization?->id);
    }
}
