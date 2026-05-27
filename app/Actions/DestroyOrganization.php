<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DestroyOrganization
{
    private readonly string $organizationName;

    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
    ) {
        $this->organizationName = $this->organization->name;
    }

    public function execute(): void
    {
        $this->validate();
        $this->delete();
        $this->log();
    }

    private function validate(): void
    {
        if ($this->user->isPartOfOrganization($this->organization) === false) {
            throw new ModelNotFoundException('Organization not found');
        }

        $member = $this->user->memberOf($this->organization);

        if (! $member->hasPermission(PermissionEnum::OrganizationDelete->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function delete(): void
    {
        $this->organization->delete();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: null,
            user: $this->user,
            action: UserActionEnum::OrganizationDeletion,
            description: sprintf('Deleted the organization called %s', $this->organizationName),
        )->onQueue('low');
    }
}
