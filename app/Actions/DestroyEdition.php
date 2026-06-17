<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Edition;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Delete an edition from an organization.
 */
class DestroyEdition
{
    private readonly string $editionTitle;

    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly Edition $edition,
    ) {
        $this->editionTitle = $this->edition->title;
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

        if ($this->edition->organization_id !== $this->organization->id) {
            throw new ModelNotFoundException('Edition not found');
        }

        if (! $member->hasPermission(PermissionEnum::EditionManage->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function delete(): void
    {
        $this->edition->delete();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::EditionDeletion,
            description: sprintf('Deleted an edition called %s', $this->editionTitle),
        )->onQueue('low');
    }
}
