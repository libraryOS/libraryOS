<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BranchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_an_organization(): void
    {
        $branch = Branch::factory()->create();

        $this->assertTrue($branch->organization()->exists());
    }

    #[Test]
    public function it_belongs_to_a_country(): void
    {
        $country = Country::factory()->create();
        $branch = Branch::factory()->create([
            'country_id' => $country->id,
        ]);

        $this->assertTrue($branch->country()->exists());
    }

    #[Test]
    public function it_formats_the_full_address(): void
    {
        $country = Country::factory()->create([
            'name' => 'United States',
        ]);

        $branch = Branch::factory()->create([
            'country_id' => $country->id,
            'address_line_1' => '1725 Slough Avenue',
            'address_line_2' => 'Suite 100',
            'city' => 'Scranton',
            'state_province' => 'PA',
            'postal_code' => '18505',
        ]);

        $this->assertSame(
            '1725 Slough Avenue, Suite 100, Scranton, PA 18505, United States',
            $branch->address->format(),
        );
    }
}
