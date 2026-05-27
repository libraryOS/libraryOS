<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Member;
use App\Models\Organization;
use App\Models\PatronType;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Delete a patron type from an organization.
 */
class DestroyPatronType
{
    private readonly string $patronTypeName;

    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly PatronType $patronType,
    ) {
        $this->patronTypeName = $this->patronType->name;
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

        if ($this->patronType->organization_id !== $this->organization->id) {
            throw new ModelNotFoundException('Patron type not found');
        }

        if (! $member->hasPermission(PermissionEnum::PatronTypeManage->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function delete(): void
    {
        $this->patronType->delete();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::PatronTypeDeletion,
            description: sprintf('Deleted a patron type called %s', $this->patronTypeName),
        )->onQueue('low');
    }
}
