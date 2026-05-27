<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Adminland;

use App\Actions\CreateLocation;
use App\Actions\DestroyLocation;
use App\Actions\UpdateLocation;
use App\Helpers\TextSanitizer;
use App\Http\Controllers\Controller;
use App\Http\Resources\LocationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LocationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $organization = $request->attributes->get('organization');

        $locations = $organization->locations()
            ->orderBy('name')
            ->get();

        return LocationResource::collection($locations);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'is_public' => ['boolean'],
            'supports_pickups' => ['boolean'],
            'supports_returns' => ['boolean'],
        ]);

        $location = new CreateLocation(
            user: $request->user(),
            organization: $organization,
            branchId: (int) $validated['branch_id'],
            name: TextSanitizer::plainText($validated['name']),
            code: isset($validated['code']) ? TextSanitizer::nullablePlainText($validated['code']) : null,
            description: isset($validated['description']) ? TextSanitizer::nullablePlainText($validated['description']) : null,
            isActive: (bool) ($validated['is_active'] ?? true),
            isPublic: (bool) ($validated['is_public'] ?? true),
            supportsPickups: (bool) ($validated['supports_pickups'] ?? false),
            supportsReturns: (bool) ($validated['supports_returns'] ?? false),
            parentId: isset($validated['parent_id']) ? (int) $validated['parent_id'] : null,
        )->execute();

        return new LocationResource($location)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id, int $locationId): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        $location = $organization->locations()->findOrFail($locationId);

        return new LocationResource($location)
            ->response()
            ->setStatusCode(200);
    }

    public function update(Request $request, int $id, int $locationId): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        $location = $organization->locations()->findOrFail($locationId);

        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'is_public' => ['boolean'],
            'supports_pickups' => ['boolean'],
            'supports_returns' => ['boolean'],
        ]);

        $location = new UpdateLocation(
            user: $request->user(),
            organization: $organization,
            location: $location,
            branchId: (int) $validated['branch_id'],
            name: TextSanitizer::plainText($validated['name']),
            code: isset($validated['code']) ? TextSanitizer::nullablePlainText($validated['code']) : null,
            description: isset($validated['description']) ? TextSanitizer::nullablePlainText($validated['description']) : null,
            isActive: (bool) ($validated['is_active'] ?? true),
            isPublic: (bool) ($validated['is_public'] ?? true),
            supportsPickups: (bool) ($validated['supports_pickups'] ?? false),
            supportsReturns: (bool) ($validated['supports_returns'] ?? false),
            parentId: isset($validated['parent_id']) ? (int) $validated['parent_id'] : null,
        )->execute();

        return new LocationResource($location)
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(Request $request, int $id, int $locationId): Response
    {
        $organization = $request->attributes->get('organization');

        $location = $organization->locations()->findOrFail($locationId);

        new DestroyLocation(
            user: $request->user(),
            organization: $organization,
            location: $location,
        )->execute();

        return response()->noContent(204);
    }
}
