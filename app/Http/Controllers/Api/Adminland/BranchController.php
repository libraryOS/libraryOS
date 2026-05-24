<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Adminland;

use App\Actions\CreateBranch;
use App\Actions\DestroyBranch;
use App\Actions\UpdateBranch;
use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BranchController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $organization = $request->attributes->get('organization');

        $branches = $organization->branches()
            ->orderBy('name')
            ->get();

        return BranchResource::collection($branches);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state_province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'integer'],
        ]);

        $branch = new CreateBranch(
            user: $request->user(),
            organization: $organization,
            name: $validated['name'],
            addressLine1: $validated['address_line_1'],
            addressLine2: $validated['address_line_2'] ?? null,
            city: $validated['city'],
            stateProvince: $validated['state_province'] ?? null,
            postalCode: $validated['postal_code'] ?? null,
            timezone: $validated['timezone'] ?? null,
            countryId: isset($validated['country_id']) ? (int) $validated['country_id'] : null,
        )->execute();

        return new BranchResource($branch)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id, int $branchId): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        $branch = $organization->branches()->findOrFail($branchId);

        return new BranchResource($branch)
            ->response()
            ->setStatusCode(200);
    }

    public function update(Request $request, int $id, int $branchId): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        $branch = $organization->branches()->findOrFail($branchId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state_province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'integer'],
        ]);

        $branch = new UpdateBranch(
            user: $request->user(),
            organization: $organization,
            branch: $branch,
            name: $validated['name'],
            addressLine1: $validated['address_line_1'],
            addressLine2: $validated['address_line_2'] ?? null,
            city: $validated['city'],
            stateProvince: $validated['state_province'] ?? null,
            postalCode: $validated['postal_code'] ?? null,
            timezone: $validated['timezone'] ?? null,
            countryId: isset($validated['country_id']) ? (int) $validated['country_id'] : null,
        )->execute();

        return new BranchResource($branch)
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(Request $request, int $id, int $branchId): Response
    {
        $organization = $request->attributes->get('organization');

        $branch = $organization->branches()->findOrFail($branchId);

        new DestroyBranch(
            user: $request->user(),
            organization: $organization,
            branch: $branch,
        )->execute();

        return response()->noContent(204);
    }
}
