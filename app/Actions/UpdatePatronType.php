<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserActionEnum;
use App\Enums\PermissionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\Member;
use App\Models\Organization;
use App\Models\PatronType;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Update a patron type for an organization.
 */
class UpdatePatronType
{
    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly PatronType $patronType,
        private string $key,
        private string $name,
        private ?string $description = null,
        private readonly bool $isActive = true,
        private readonly ?int $membershipDurationDays = null,
        private readonly ?int $maxLoans = null,
        private readonly bool $keepLoanHistory = false,
        private readonly bool $canReceiveNotifications = true,
        private readonly ?int $minimumAge = null,
        private readonly ?int $maximumAge = null,
    ) {}

    public function execute(): PatronType
    {
        $this->sanitize();
        $this->validate();
        $this->update();
        $this->log();

        return $this->patronType;
    }

    private function sanitize(): void
    {
        $this->key = TextSanitizer::plainText($this->key);
        $this->name = TextSanitizer::plainText($this->name);
        $this->description = TextSanitizer::nullablePlainText($this->description);
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

    private function update(): void
    {
        $this->patronType->update([
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'membership_duration_days' => $this->membershipDurationDays,
            'max_loans' => $this->maxLoans,
            'keep_loan_history' => $this->keepLoanHistory,
            'can_receive_notifications' => $this->canReceiveNotifications,
            'minimum_age' => $this->minimumAge,
            'maximum_age' => $this->maximumAge,
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::PatronTypeUpdate,
            description: sprintf('Updated a patron type called %s', $this->patronType->name),
        )->onQueue('low');
    }
}
