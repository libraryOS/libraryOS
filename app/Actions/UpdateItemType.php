<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\ItemType;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Update an item type for an organization.
 */
class UpdateItemType
{
    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly ItemType $itemType,
        private string $key,
        private ?string $name = null,
        private ?string $nameTranslationKey = null,
        private ?string $description = null,
        private readonly bool $isLoanable = true,
        private readonly bool $isHoldable = true,
        private readonly bool $isVisibleInCatalog = true,
        private readonly ?int $defaultLoanDays = null,
    ) {}

    public function execute(): ItemType
    {
        $this->sanitize();
        $this->validate();
        $this->update();
        $this->log();

        return $this->itemType;
    }

    private function sanitize(): void
    {
        $this->key = TextSanitizer::plainText($this->key);
        $this->name = TextSanitizer::nullablePlainText($this->name);
        $this->nameTranslationKey = TextSanitizer::nullablePlainText($this->nameTranslationKey);
        $this->description = TextSanitizer::nullablePlainText($this->description);
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

    private function update(): void
    {
        $this->itemType->update([
            'key' => $this->key,
            'name' => $this->name,
            'name_translation_key' => $this->nameTranslationKey,
            'description' => $this->description,
            'is_loanable' => $this->isLoanable,
            'is_holdable' => $this->isHoldable,
            'is_visible_in_catalog' => $this->isVisibleInCatalog,
            'default_loan_days' => $this->defaultLoanDays,
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: 'item_type_update',
            description: sprintf('Updated an item type called %s', $this->itemType->getName()),
        )->onQueue('low');
    }
}
