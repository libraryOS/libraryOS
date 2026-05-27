<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Destroy a role for an organization.
 */
class DestroyRole
{
    private readonly string $roleName;

    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly Role $role,
    ) {
        $this->roleName = $this->role->getName();
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

        if ($this->role->organization_id !== $this->organization->id) {
            throw new ModelNotFoundException('Role not found');
        }

        if ($this->role->is_system) {
            throw new ModelNotFoundException('Role not found');
        }

        if (! $member->hasPermission(PermissionEnum::RoleManage->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function delete(): void
    {
        $this->role->delete();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::RoleDeletion,
            description: sprintf('Deleted a role called %s', $this->roleName),
        )->onQueue('low');
    }
}
