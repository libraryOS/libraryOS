<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\ItemType;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Delete an item type from an organization.
 */
class DestroyItemType
{
    private readonly string $itemTypeName;

    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly ItemType $itemType,
    ) {
        $this->itemTypeName = $this->itemType->getName();
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

        if ($this->itemType->organization_id !== $this->organization->id) {
            throw new ModelNotFoundException('Item type not found');
        }

        if (! $member->hasPermission(PermissionEnum::ItemTypeManage->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function delete(): void
    {
        $this->itemType->delete();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::ItemTypeDeletion,
            description: sprintf('Deleted an item type called %s', $this->itemTypeName),
        )->onQueue('low');
    }
}
