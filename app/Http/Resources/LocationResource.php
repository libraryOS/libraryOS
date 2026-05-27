<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Location
 */
class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'location',
            'id' => (string) $this->id,
            'attributes' => [
                'organization_id' => $this->organization_id,
                'branch_id' => $this->branch_id,
                'parent_id' => $this->parent_id,
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'is_active' => $this->is_active,
                'is_public' => $this->is_public,
                'supports_pickups' => $this->supports_pickups,
                'supports_returns' => $this->supports_returns,
                'created_at' => $this->created_at->timestamp,
                'updated_at' => $this->updated_at->timestamp,
            ],
            'links' => [
                'self' => route('api.organization.adminland.location.show', [
                    'id' => $this->organization_id,
                    'locationId' => $this->id,
                ]),
            ],
        ];
    }
}
