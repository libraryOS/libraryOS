<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Jobs\LogUserAction;
use App\Models\Location;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DestroyLocation
{
    private readonly string $locationName;

    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly Location $location,
    ) {
        $this->locationName = $this->location->name;
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

        if ($this->location->organization_id !== $this->organization->id) {
            throw new ModelNotFoundException('Location not found');
        }

        if (! $member->hasPermission(PermissionEnum::LocationManage->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function delete(): void
    {
        $this->location->delete();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: 'location_deletion',
            description: sprintf('Deleted a location called %s', $this->locationName),
        )->onQueue('low');
    }
}
