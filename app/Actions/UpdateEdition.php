<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PermissionEnum;
use App\Enums\UserActionEnum;
use App\Helpers\TextSanitizer;
use App\Jobs\LogUserAction;
use App\Models\Edition;
use App\Models\ItemType;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Update an edition for an organization.
 */
class UpdateEdition
{
    public function __construct(
        private readonly User $user,
        private readonly Organization $organization,
        private readonly Edition $edition,
        private readonly Work $work,
        private readonly ItemType $itemType,
        private string $title,
        private ?string $isbn = null,
        private ?string $publisher = null,
        private readonly ?int $publicationYear = null,
        private ?string $language = null,
        private ?string $coverImagePath = null,
    ) {}

    public function execute(): Edition
    {
        $this->sanitize();
        $this->validate();
        $this->update();
        $this->log();

        return $this->edition;
    }

    private function sanitize(): void
    {
        $this->title = TextSanitizer::plainText($this->title);
        $this->isbn = TextSanitizer::nullablePlainText($this->isbn);
        $this->publisher = TextSanitizer::nullablePlainText($this->publisher);
        $this->language = TextSanitizer::nullablePlainText($this->language);
        $this->coverImagePath = TextSanitizer::nullablePlainText($this->coverImagePath);
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

        if ($this->work->organization_id !== $this->organization->id) {
            throw new ModelNotFoundException('Work not found');
        }

        if ($this->itemType->organization_id !== $this->organization->id) {
            throw new ModelNotFoundException('Item type not found');
        }

        if (! $member->hasPermission(PermissionEnum::EditionManage->value)) {
            throw new ModelNotFoundException('Permission denied');
        }
    }

    private function update(): void
    {
        $this->edition->update([
            'work_id' => $this->work->id,
            'item_type_id' => $this->itemType->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'publisher' => $this->publisher,
            'publication_year' => $this->publicationYear,
            'language' => $this->language,
            'cover_image_path' => $this->coverImagePath,
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            organization: $this->organization,
            user: $this->user,
            action: UserActionEnum::EditionUpdate,
            description: sprintf('Updated an edition called %s', $this->edition->title),
        )->onQueue('low');
    }
}
