<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Adminland;

use App\Actions\CreateItemType;
use App\Actions\DestroyItemType;
use App\Actions\UpdateItemType;
use App\Helpers\TextSanitizer;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemTypeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ItemTypeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $organization = $request->attributes->get('organization');

        $itemTypes = $organization->itemTypes()
            ->orderBy('name')
            ->get();

        return ItemTypeResource::collection($itemTypes);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_loanable' => ['boolean'],
            'is_holdable' => ['boolean'],
            'is_visible_in_catalog' => ['boolean'],
            'default_loan_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $itemType = new CreateItemType(
            user: $request->user(),
            organization: $organization,
            key: TextSanitizer::plainText($validated['key']),
            name: isset($validated['name']) ? TextSanitizer::nullablePlainText($validated['name']) : null,
            description: isset($validated['description']) ? TextSanitizer::nullablePlainText($validated['description']) : null,
            isLoanable: (bool) ($validated['is_loanable'] ?? true),
            isHoldable: (bool) ($validated['is_holdable'] ?? true),
            isVisibleInCatalog: (bool) ($validated['is_visible_in_catalog'] ?? true),
            defaultLoanDays: isset($validated['default_loan_days']) ? (int) $validated['default_loan_days'] : null,
        )->execute();

        return new ItemTypeResource($itemType)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id, int $itemTypeId): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        $itemType = $organization->itemTypes()->findOrFail($itemTypeId);

        return new ItemTypeResource($itemType)
            ->response()
            ->setStatusCode(200);
    }

    public function update(Request $request, int $id, int $itemTypeId): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        $itemType = $organization->itemTypes()->findOrFail($itemTypeId);

        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_loanable' => ['boolean'],
            'is_holdable' => ['boolean'],
            'is_visible_in_catalog' => ['boolean'],
            'default_loan_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $itemType = new UpdateItemType(
            user: $request->user(),
            organization: $organization,
            itemType: $itemType,
            key: TextSanitizer::plainText($validated['key']),
            name: isset($validated['name']) ? TextSanitizer::nullablePlainText($validated['name']) : null,
            description: isset($validated['description']) ? TextSanitizer::nullablePlainText($validated['description']) : null,
            isLoanable: (bool) ($validated['is_loanable'] ?? true),
            isHoldable: (bool) ($validated['is_holdable'] ?? true),
            isVisibleInCatalog: (bool) ($validated['is_visible_in_catalog'] ?? true),
            defaultLoanDays: isset($validated['default_loan_days']) ? (int) $validated['default_loan_days'] : null,
        )->execute();

        return new ItemTypeResource($itemType)
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(Request $request, int $id, int $itemTypeId): Response
    {
        $organization = $request->attributes->get('organization');

        $itemType = $organization->itemTypes()->findOrFail($itemTypeId);

        new DestroyItemType(
            user: $request->user(),
            organization: $organization,
            itemType: $itemType,
        )->execute();

        return response()->noContent(204);
    }
}
