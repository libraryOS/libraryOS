<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\UserActionEnum;
use App\Models\Organization;
use App\Models\Patron;
use App\Models\PatronLog;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class LogPatronAction implements ShouldQueue
{
    use Queueable;

    /**
     * @param  User|Patron|null  $actor  The model that performed the action.
     * @param  array<string, mixed>|null  $metadata  Additional context for the log entry.
     */
    public function __construct(
        public Organization $organization,
        public Patron $patron,
        public User|Patron|null $actor,
        public UserActionEnum $action,
        public ?string $description = null,
        public ?array $metadata = null,
    ) {}

    /**
     * Log the patron action in the patron_logs table.
     */
    public function handle(): void
    {
        PatronLog::query()->create([
            'organization_id' => $this->organization->id,
            'patron_id' => $this->patron->id,
            'actor_type' => $this->actor ? $this->actor::class : null,
            'actor_id' => $this->actor?->id,
            'action' => $this->action->value,
            'description' => $this->description,
            'metadata' => $this->metadata,
        ]);
    }
}
