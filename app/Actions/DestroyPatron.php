<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Patron;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DestroyPatron
{
    private readonly string $patronName;

    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly Patron $patron,
    ) {
        $this->patronName = $this->patron->first_name.' '.$this->patron->last_name;
    }

    public function execute(): void
    {
        $this->validate();
        $this->archive();
        $this->log();
    }

    private function validate(): void
    {
        $member = $this->user->memberOf($this->organization);

        if (! $member instanceof Member) {
            throw new ModelNotFoundException('Organization not found');
        }

        if ($this->patron->organization_id !== $this->organization->id) {
            throw new ModelNotFoundException('Patron not found');
        }

        if (! $member->hasPermission(PermissionEnum::PatronArchive->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function archive(): void
    {
        $this->patron->update([
            'status' => 'archived',
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::PatronArchive,
            description: sprintf('Archived a patron called %s', $this->patronName),
        )->onQueue('low');
    }
}
