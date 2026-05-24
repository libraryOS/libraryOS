<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_many_branches(): void
    {
        $country = Country::factory()->create();
        Branch::factory()->create([
            'country_id' => $country->id,
        ]);

        $this->assertTrue($country->branches()->exists());
    }
}
