<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ItemType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ItemType
 */
class ItemTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'item_type',
            'id' => (string) $this->id,
            'attributes' => [
                'key' => $this->key,
                'name' => $this->name,
                'description' => $this->description,
                'is_loanable' => $this->is_loanable,
                'is_holdable' => $this->is_holdable,
                'is_visible_in_catalog' => $this->is_visible_in_catalog,
                'default_loan_days' => $this->default_loan_days,
                'created_at' => $this->created_at->timestamp,
                'updated_at' => $this->updated_at->timestamp,
            ],
            'links' => [
                'self' => route('api.organization.adminland.item-type.show', [
                    'id' => $this->organization_id,
                    'itemTypeId' => $this->id,
                ]),
            ],
        ];
    }
}
