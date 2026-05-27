<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateOrganization
{
    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private string $name,
    ) {}

    public function execute(): Organization
    {
        $this->sanitize();
        $this->validate();
        $this->rename();
        $this->log();

        return $this->organization;
    }

    private function sanitize(): void
    {
        $this->name = TextSanitizer::plainText($this->name);
    }

    private function validate(): void
    {
        if ($this->user->isPartOfOrganization($this->organization) === false) {
            throw new ModelNotFoundException('Organization not found');
        }

        if (in_array(preg_match('/^[a-zA-Z0-9\s\-_]+$/', $this->name), [0, false], true)) {
            throw ValidationException::withMessages([
                'organization_name' => 'Organization name can only contain letters, numbers, spaces, hyphens and underscores',
            ]);
        }

        $member = $this->user->memberOf($this->organization);

        if (! $member->hasPermission(PermissionEnum::OrganizationUpdate->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function rename(): void
    {
        $this->organization->update([
            'name' => $this->name,
            'slug' => $this->organization->id.'-'.Str::of($this->name)->slug('-'),
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::OrganizationUpdate,
            description: sprintf('Updated the organization called %s', $this->name),
        )->onQueue('low');
    }
}
