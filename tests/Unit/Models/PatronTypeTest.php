<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\PatronType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PatronTypeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_an_organization(): void
    {
        $patronType = PatronType::factory()->create();

        $this->assertTrue($patronType->organization()->exists());
    }
}
