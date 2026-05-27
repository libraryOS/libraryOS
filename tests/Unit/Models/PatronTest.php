<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\Organization;
use App\Models\Patron;
use App\Models\PatronLog;
use App\Models\PatronType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PatronTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_an_organization(): void
    {
        $organization = Organization::factory()->create();
        $patronType = PatronType::factory()->create(['organization_id' => $organization->id]);
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);

        $this->assertTrue($patron->organization()->exists());
    }

    #[Test]
    public function it_can_belong_to_a_user(): void
    {
        $organization = Organization::factory()->create();
        $patronType = PatronType::factory()->create(['organization_id' => $organization->id]);
        $user = $this->createUser();
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);

        $this->assertTrue($patron->user()->exists());
    }

    #[Test]
    public function it_belongs_to_a_patron_type(): void
    {
        $organization = Organization::factory()->create();
        $patronType = PatronType::factory()->create(['organization_id' => $organization->id]);
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);

        $this->assertTrue($patron->patronType()->exists());
    }

    #[Test]
    public function it_can_belong_to_a_home_branch(): void
    {
        $organization = Organization::factory()->create();
        $branch = Branch::factory()->create(['organization_id' => $organization->id]);
        $patronType = PatronType::factory()->create(['organization_id' => $organization->id]);
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => $branch->id,
        ]);

        $this->assertTrue($patron->homeBranch()->exists());
        $this->assertEquals($branch->id, $patron->homeBranch?->id);
    }

    #[Test]
    public function it_has_no_user_or_home_branch_by_default(): void
    {
        $patron = Patron::factory()->create([
            'user_id' => null,
            'home_branch_id' => null,
        ]);

        $this->assertNull($patron->user_id);
        $this->assertNull($patron->home_branch_id);
        $this->assertFalse($patron->user()->exists());
        $this->assertFalse($patron->homeBranch()->exists());
    }

    #[Test]
    public function it_has_many_patron_logs(): void
    {
        $organization = Organization::factory()->create();
        $patronType = PatronType::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $patron = Patron::factory()->create([
            'organization_id' => $organization->id,
            'patron_type_id' => $patronType->id,
            'home_branch_id' => null,
        ]);

        PatronLog::factory()->create([
            'organization_id' => $organization->id,
            'patron_id' => $patron->id,
        ]);

        $this->assertTrue($patron->patronLogs()->exists());
    }

    #[Test]
    public function it_has_many_patron_logs_as_actor(): void
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

        PatronLog::factory()->create([
            'organization_id' => $organization->id,
            'patron_id' => $subjectPatron->id,
            'actor_type' => Patron::class,
            'actor_id' => $actorPatron->id,
        ]);

        $this->assertTrue($actorPatron->patronLogsAsActor()->exists());
    }
}
