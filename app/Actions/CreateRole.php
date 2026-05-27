<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Create a role for an organization.
 */
class CreateRole
{
    private Role $role;

    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private string $key,
        private string $name,
        private ?string $description = null,
    ) {}

    public function execute(): Role
    {
        $this->sanitize();
        $this->validate();
        $this->create();
        $this->log();

        return $this->role;
    }

    private function sanitize(): void
    {
        $this->key = TextSanitizer::plainText($this->key);
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

        if (! $member->hasPermission(PermissionEnum::RoleManage->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function create(): void
    {
        $this->role = Role::query()->create([
            'organization_id' => $this->organization->id,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'is_system' => false,
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::RoleCreation,
            description: sprintf('Created a role called %s', $this->name),
        )->onQueue('low');
    }
}
