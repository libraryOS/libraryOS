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
 * Update a work for an organization.
 */
class UpdateWork
{
    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly Work $work,
        private string $title,
        private ?string $subtitle = null,
        private ?string $description = null,
    ) {}

    public function execute(): Work
    {
        $this->sanitize();
        $this->validate();
        $this->update();
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

        if ($this->work->organization_id !== $this->organization->id) {
            throw new ModelNotFoundException('Work not found');
        }

        if (! $member->hasPermission(PermissionEnum::WorkManage->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function update(): void
    {
        $this->work->update([
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
            action: UserActionEnum::WorkUpdate,
            description: sprintf('Updated a work called %s', $this->work->title),
        )->onQueue('low');
    }
}
