<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserActionEnum;
use App\Enums\PermissionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Update a role for an organization.
 */
class UpdateRole
{
    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly Role $role,
        private string $name,
        private ?string $description = null,
    ) {}

    public function execute(): Role
    {
        $this->sanitize();
        $this->validate();
        $this->update();
        $this->log();

        return $this->role;
    }

    private function sanitize(): void
    {
        $this->name = TextSanitizer::plainText($this->name);

        if ($this->description !== null) {
            $this->description = TextSanitizer::plainText($this->description);
        }
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

    private function update(): void
    {
        $this->role->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::RoleUpdate,
            description: sprintf('Updated a role called %s', $this->name),
        )->onQueue('low');
    }
}
