<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Delete a work from an organization.
 */
class DestroyWork
{
    private readonly string $workTitle;

    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly Work $work,
    ) {
        $this->workTitle = $this->work->title;
    }

    public function execute(): void
    {
        $this->validate();
        $this->delete();
        $this->log();
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

    private function delete(): void
    {
        $this->work->delete();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::WorkDeletion,
            description: sprintf('Deleted a work called %s', $this->workTitle),
        )->onQueue('low');
    }
}
