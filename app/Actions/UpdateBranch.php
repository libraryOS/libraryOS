<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class UpdateBranch
{
    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly Branch $branch,
        private string $name,
        private string $addressLine1,
        private ?string $addressLine2,
        private string $city,
        private ?string $stateProvince,
        private ?string $postalCode,
        private ?string $timezone,
        private ?string $code = null,
        private ?string $description = null,
        private readonly ?int $countryId = null,
    ) {}

    public function execute(): Branch
    {
        $this->sanitize();
        $this->validate();
        $this->update();
        $this->log();

        return $this->branch;
    }

    private function sanitize(): void
    {
        $this->name = TextSanitizer::plainText($this->name);
        $this->addressLine1 = TextSanitizer::plainText($this->addressLine1);
        $this->addressLine2 = TextSanitizer::nullablePlainText($this->addressLine2);
        $this->city = TextSanitizer::plainText($this->city);
        $this->stateProvince = TextSanitizer::nullablePlainText($this->stateProvince);
        $this->postalCode = TextSanitizer::nullablePlainText($this->postalCode);
        $this->timezone = TextSanitizer::nullablePlainText($this->timezone);
        $this->code = TextSanitizer::nullablePlainText($this->code);
        $this->description = TextSanitizer::nullablePlainText($this->description);
    }

    private function validate(): void
    {
        $member = $this->user->memberOf($this->organization);

        if (! $member instanceof Member) {
            throw new ModelNotFoundException('Organization not found');
        }

        if ($this->branch->organization_id !== $this->organization->id) {
            throw new ModelNotFoundException('Branch not found');
        }

        if (! $member->hasPermission(PermissionEnum::BranchManage->value)) {
            throw new ModelNotFoundException('Permission denied');
        }

        if ($this->countryId !== null && Country::query()->whereKey($this->countryId)->exists() === false) {
            throw new ModelNotFoundException('Country not found');
        }
    }

    private function update(): void
    {
        $this->branch->update([
            'country_id' => $this->countryId,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'slug' => $this->branch->id.'-'.Str::of($this->name)->slug('-'),
            'address_line_1' => $this->addressLine1,
            'address_line_2' => $this->addressLine2,
            'city' => $this->city,
            'state_province' => $this->stateProvince,
            'postal_code' => $this->postalCode,
            'timezone' => $this->timezone,
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::BranchUpdate,
            description: sprintf('Updated a branch called %s', $this->name),
        )->onQueue('low');
    }
}
