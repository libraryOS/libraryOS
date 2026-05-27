<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Branch;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DestroyBranch
{
    private readonly string $branchName;

    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly Branch $branch,
    ) {
        $this->branchName = $this->branch->name;
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

        if ($this->branch->organization_id !== $this->organization->id) {
            throw new ModelNotFoundException('Branch not found');
        }

        if (! $member->hasPermission(PermissionEnum::BranchManage->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function delete(): void
    {
        $this->branch->delete();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::BranchDeletion,
            description: sprintf('Deleted a branch called %s', $this->branchName),
        )->onQueue('low');
    }
}
