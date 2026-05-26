<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Adminland;

use App\Actions\CreatePatronType;
use App\Actions\DestroyPatronType;
use App\Actions\UpdatePatronType;
use App\Helpers\TextSanitizer;
use App\Http\Controllers\Controller;
use App\Http\Resources\PatronTypeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PatronTypeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $organization = $request->attributes->get('organization');

        $patronTypes = $organization->patronTypes()
            ->orderBy('name')
            ->get();

        return PatronTypeResource::collection($patronTypes);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'membership_duration_days' => ['nullable', 'integer', 'min:1'],
            'max_loans' => ['nullable', 'integer', 'min:1'],
            'keep_loan_history' => ['boolean'],
            'can_receive_notifications' => ['boolean'],
            'minimum_age' => ['nullable', 'integer', 'min:0'],
            'maximum_age' => ['nullable', 'integer', 'min:0'],
        ]);

        $patronType = new CreatePatronType(
            user: $request->user(),
            organization: $organization,
            key: TextSanitizer::plainText($validated['key']),
            name: TextSanitizer::plainText($validated['name']),
            description: isset($validated['description']) ? TextSanitizer::nullablePlainText($validated['description']) : null,
            isActive: (bool) ($validated['is_active'] ?? true),
            membershipDurationDays: isset($validated['membership_duration_days']) ? (int) $validated['membership_duration_days'] : null,
            maxLoans: isset($validated['max_loans']) ? (int) $validated['max_loans'] : null,
            keepLoanHistory: (bool) ($validated['keep_loan_history'] ?? false),
            canReceiveNotifications: (bool) ($validated['can_receive_notifications'] ?? true),
            minimumAge: isset($validated['minimum_age']) ? (int) $validated['minimum_age'] : null,
            maximumAge: isset($validated['maximum_age']) ? (int) $validated['maximum_age'] : null,
        )->execute();

        return new PatronTypeResource($patronType)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id, int $patronTypeId): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        $patronType = $organization->patronTypes()->findOrFail($patronTypeId);

        return new PatronTypeResource($patronType)
            ->response()
            ->setStatusCode(200);
    }

    public function update(Request $request, int $id, int $patronTypeId): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        $patronType = $organization->patronTypes()->findOrFail($patronTypeId);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'membership_duration_days' => ['nullable', 'integer', 'min:1'],
            'max_loans' => ['nullable', 'integer', 'min:1'],
            'keep_loan_history' => ['boolean'],
            'can_receive_notifications' => ['boolean'],
            'minimum_age' => ['nullable', 'integer', 'min:0'],
            'maximum_age' => ['nullable', 'integer', 'min:0'],
        ]);

        $patronType = new UpdatePatronType(
            user: $request->user(),
            organization: $organization,
            patronType: $patronType,
            key: TextSanitizer::plainText($validated['key']),
            name: TextSanitizer::plainText($validated['name']),
            description: isset($validated['description']) ? TextSanitizer::nullablePlainText($validated['description']) : null,
            isActive: (bool) ($validated['is_active'] ?? true),
            membershipDurationDays: isset($validated['membership_duration_days']) ? (int) $validated['membership_duration_days'] : null,
            maxLoans: isset($validated['max_loans']) ? (int) $validated['max_loans'] : null,
            keepLoanHistory: (bool) ($validated['keep_loan_history'] ?? false),
            canReceiveNotifications: (bool) ($validated['can_receive_notifications'] ?? true),
            minimumAge: isset($validated['minimum_age']) ? (int) $validated['minimum_age'] : null,
            maximumAge: isset($validated['maximum_age']) ? (int) $validated['maximum_age'] : null,
        )->execute();

        return new PatronTypeResource($patronType)
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(Request $request, int $id, int $patronTypeId): Response
    {
        $organization = $request->attributes->get('organization');

        $patronType = $organization->patronTypes()->findOrFail($patronTypeId);

        new DestroyPatronType(
            user: $request->user(),
            organization: $organization,
            patronType: $patronType,
        )->execute();

        return response()->noContent(204);
    }
}
