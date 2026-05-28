<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Enums\UserActionEnum;
use App\Jobs\LogPatronAction;
use App\Models\Organization;
use App\Models\Patron;
use App\Models\PatronLog;
use App\Models\PatronType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogPatronActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_logs_patron_action_with_user_actor(): void
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
        $user = User::factory()->create();

        LogPatronAction::dispatch(
            organization: $organization,
            patron: $patron,
            actor: $user,
            action: UserActionEnum::PatronUpdate,
            description: 'Updated patron address information',
            metadata: [
                'source' => 'web',
                'changes' => ['address_line_1'],
            ],
        );

        $patronLog = PatronLog::query()->first();

        $this->assertNotNull($patronLog);
        $this->assertSame($organization->id, $patronLog->organization_id);
        $this->assertSame($patron->id, $patronLog->patron_id);
        $this->assertSame(User::class, $patronLog->actor_type);
        $this->assertSame($user->id, $patronLog->actor_id);
        $this->assertSame('patron_update', $patronLog->action);
        $this->assertSame('Updated patron address information', $patronLog->description);
        $this->assertSame('web', $patronLog->metadata['source']);
    }

    #[Test]
    public function it_logs_patron_action_without_actor(): void
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

        LogPatronAction::dispatch(
            organization: $organization,
            patron: $patron,
            actor: null,
            action: UserActionEnum::PatronArchive,
            description: 'Archived inactive patron account',
        );

        $patronLog = PatronLog::query()->first();

        $this->assertNotNull($patronLog);
        $this->assertNull($patronLog->actor_type);
        $this->assertNull($patronLog->actor_id);
        $this->assertSame('patron_archive', $patronLog->action);
    }
}
