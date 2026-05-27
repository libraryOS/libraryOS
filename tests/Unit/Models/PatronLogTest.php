<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Organization;
use App\Models\Patron;
use App\Models\PatronLog;
use App\Models\PatronType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PatronLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure a patron log belongs to an organization.
     */
    #[Test]
    public function it_belongs_to_an_organization(): void
    {
        $patronLog = PatronLog::factory()->create();

        $this->assertTrue($patronLog->organization()->exists());
    }

    #[Test]
    public function it_belongs_to_a_patron(): void
    {
        $patronLog = PatronLog::factory()->create();

        $this->assertTrue($patronLog->patron()->exists());
    }

    #[Test]
    public function it_can_have_a_user_actor(): void
    {
        $patronLog = PatronLog::factory()->create();

        $this->assertInstanceOf(User::class, $patronLog->actor);
    }

    #[Test]
    public function it_can_have_a_patron_actor(): void
    {
        $organization = Organization::factory()->create();
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $subjectPatron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);
        $actorPatron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);

        $patronLog = PatronLog::factory()->create([
            'organization_id' => $organization->id,
            'patron_id' => $subjectPatron->id,
            'actor_type' => Patron::class,
            'actor_id' => $actorPatron->id,
        ]);

        $this->assertInstanceOf(Patron::class, $patronLog->actor);
        $this->assertSame($actorPatron->id, $patronLog->actor?->id);
    }

    #[Test]
    public function it_can_have_no_actor(): void
    {
        $patronLog = PatronLog::factory()->withoutActor()->create();

        $this->assertNull($patronLog->actor_type);
        $this->assertNull($patronLog->actor_id);
        $this->assertNull($patronLog->actor);
    }

    #[Test]
    public function it_casts_metadata_to_array(): void
    {
        $patronLog = PatronLog::factory()->create([
            'metadata' => [
                'channel' => 'manual',
                'changes' => ['status' => ['from' => 'active', 'to' => 'inactive']],
            ],
        ]);

        $this->assertIsArray($patronLog->metadata);
        $this->assertSame('manual', $patronLog->metadata['channel']);
    }
}
