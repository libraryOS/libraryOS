<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PatronType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PatronType
 */
class PatronTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'patron_type',
            'id' => (string) $this->id,
            'attributes' => [
                'key' => $this->key,
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->is_active,
                'membership_duration_days' => $this->membership_duration_days,
                'max_loans' => $this->max_loans,
                'keep_loan_history' => $this->keep_loan_history,
                'can_receive_notifications' => $this->can_receive_notifications,
                'minimum_age' => $this->minimum_age,
                'maximum_age' => $this->maximum_age,
                'created_at' => $this->created_at->timestamp,
                'updated_at' => $this->updated_at->timestamp,
            ],
            'links' => [
                'self' => route('api.organization.adminland.patron-type.show', [
                    'id' => $this->organization_id,
                    'patronTypeId' => $this->id,
                ]),
            ],
        ];
    }
}
