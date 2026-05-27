<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserActionEnum;
use App\Enums\PermissionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\Branch;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Patron;
use App\Models\PatronType;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatePatron
{
    private Patron $patron;

    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly ?int $userId,
        private readonly int $patronTypeId,
        private readonly ?int $homeBranchId,
        private string $cardNumber,
        private string $firstName,
        private string $lastName,
        private ?string $email = null,
        private ?string $phone = null,
        private string $status = 'active',
        private readonly ?string $membershipExpiresAt = null,
        private ?string $notes = null,
    ) {}

    public function execute(): Patron
    {
        $this->sanitize();
        $this->validate();
        $this->create();
        $this->log();

        return $this->patron;
    }

    private function sanitize(): void
    {
        $this->cardNumber = TextSanitizer::plainText($this->cardNumber);
        $this->firstName = TextSanitizer::plainText($this->firstName);
        $this->lastName = TextSanitizer::plainText($this->lastName);
        $this->email = TextSanitizer::nullablePlainText($this->email);
        $this->phone = TextSanitizer::nullablePlainText($this->phone);
        $this->status = TextSanitizer::plainText($this->status);
        $this->notes = TextSanitizer::nullablePlainText($this->notes);
    }

    private function validate(): void
    {
        $member = $this->user->memberOf($this->organization);

        if (! $member instanceof Member) {
            throw new ModelNotFoundException('Organization not found');
        }

        if (! $member->hasPermission(PermissionEnum::PatronCreate->value)) {
            throw new ModelNotFoundException('Permission denied');
        }

        if (! PatronType::query()->where('id', $this->patronTypeId)->where('organization_id', $this->organization->id)->exists()) {
            throw new ModelNotFoundException('Patron type not found');
        }

        if ($this->homeBranchId !== null && ! Branch::query()->where('id', $this->homeBranchId)->where('organization_id', $this->organization->id)->exists()) {
            throw new ModelNotFoundException('Branch not found');
        }

        if ($this->userId !== null && ! User::query()->whereKey($this->userId)->exists()) {
            throw new ModelNotFoundException('User not found');
        }
    }

    private function create(): void
    {
        $this->patron = Patron::query()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->userId,
            'patron_type_id' => $this->patronTypeId,
            'home_branch_id' => $this->homeBranchId,
            'card_number' => $this->cardNumber,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'membership_expires_at' => $this->membershipExpiresAt,
            'notes' => $this->notes,
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::PatronCreation,
            description: sprintf('Created a patron called %s %s', $this->firstName, $this->lastName),
        )->onQueue('low');
    }
}
