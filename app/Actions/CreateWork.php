<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Create a work for an organization.
 */
class CreateWork
{
    private Work $work;

    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private string $title,
        private ?string $subtitle = null,
        private ?string $description = null,
    ) {}

    public function execute(): Work
    {
        $this->sanitize();
        $this->validate();
        $this->create();
        $this->log();

        return $this->work;
    }

    private function sanitize(): void
    {
        $this->title = TextSanitizer::plainText($this->title);
        $this->subtitle = TextSanitizer::nullablePlainText($this->subtitle);
        $this->description = TextSanitizer::nullablePlainText($this->description);
    }

    private function validate(): void
    {
        $member = $this->user->memberOf($this->organization);

        if (! $member instanceof Member) {
            throw new ModelNotFoundException('Organization not found');
        }

        if (! $member->hasPermission(PermissionEnum::WorkManage->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function create(): void
    {
        $this->work = Work::query()->create([
            'organization_id' => $this->organization->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::WorkCreation,
            description: sprintf('Created a work called %s', $this->work->title),
        )->onQueue('low');
    }
}
