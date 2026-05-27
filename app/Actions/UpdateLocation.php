<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserActionEnum;
use App\Enums\PermissionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\Branch;
use App\Models\Location;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateLocation
{
    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly Location $location,
        private readonly int $branchId,
        private string $name,
        private ?string $code = null,
        private ?string $description = null,
        private readonly bool $isActive = true,
        private readonly bool $isPublic = true,
        private readonly bool $supportsPickups = false,
        private readonly bool $supportsReturns = false,
        private readonly ?int $parentId = null,
    ) {}

    public function execute(): Location
    {
        $this->sanitize();
        $this->validate();
        $this->update();
        $this->log();

        return $this->location;
    }

    private function sanitize(): void
    {
        $this->name = TextSanitizer::plainText($this->name);
        $this->code = TextSanitizer::nullablePlainText($this->code);
        $this->description = TextSanitizer::nullablePlainText($this->description);
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

        if (! Branch::query()->where('id', $this->branchId)->where('organization_id', $this->organization->id)->exists()) {
            throw new ModelNotFoundException('Branch not found');
        }

        if ($this->parentId !== null && ! Location::query()->where('id', $this->parentId)->where('organization_id', $this->organization->id)->exists()) {
            throw new ModelNotFoundException('Parent location not found');
        }
    }

    private function update(): void
    {
        $this->location->update([
            'branch_id' => $this->branchId,
            'parent_id' => $this->parentId,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'is_public' => $this->isPublic,
            'supports_pickups' => $this->supportsPickups,
            'supports_returns' => $this->supportsReturns,
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::LocationUpdate,
            description: sprintf('Updated a location called %s', $this->name),
        )->onQueue('low');
    }
}
