<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Organization\Adminland;

use App\Actions\CreateLocation;
use App\Actions\DestroyLocation;
use App\Actions\UpdateLocation;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('location.manage')) {
            abort(403);
        }

        [$branches, $locationsByBranch] = $this->indexViewData($organization);

        return view('app.organization.adminland.locations.index', [
            'hasBranches' => $branches->isNotEmpty(),
            'locationsByBranch' => $locationsByBranch,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('location.manage')) {
            abort(403);
        }

        [$branches, , $branchOptions, $parentLocationOptions] = $this->formViewData($organization, null);

        if ($branches->isEmpty()) {
            return to_route('organization.adminland.location.index', $organization->slug);
        }

        return view('app.organization.adminland.locations.create', [
            'branchOptions' => $branchOptions,
            'parentLocationOptions' => $parentLocationOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('location.manage')) {
            abort(403);
        }

        $validated = $this->validateRequest($request);

        new CreateLocation(
            user: $request->user(),
            organization: $organization,
            branchId: (int) $validated['branch_id'],
            name: $validated['name'],
            code: $validated['code'] ?? null,
            description: $validated['description'] ?? null,
            isActive: $request->boolean('is_active'),
            isPublic: $request->boolean('is_public'),
            supportsPickups: $request->boolean('supports_pickups'),
            supportsReturns: $request->boolean('supports_returns'),
            parentId: isset($validated['parent_id']) ? (int) $validated['parent_id'] : null,
        )->execute();

        return to_route('organization.adminland.location.index', $organization->slug)
            ->with('status', trans('Changes saved'));
    }

    public function edit(Request $request): View
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('location.manage')) {
            abort(403);
        }

        $id = $request->route()->parameter('location');

        try {
            $location = $organization->locations()->findOrFail($id);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        [, , $branchOptions, $parentLocationOptions] = $this->formViewData($organization, $location);

        return view('app.organization.adminland.locations.edit', [
            'location' => $location,
            'branchOptions' => $branchOptions,
            'parentLocationOptions' => $parentLocationOptions,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('location.manage')) {
            abort(403);
        }

        $id = $request->route()->parameter('location');

        try {
            $location = $organization->locations()->findOrFail($id);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        $validated = $this->validateRequest($request);

        new UpdateLocation(
            user: $request->user(),
            organization: $organization,
            location: $location,
            branchId: (int) $validated['branch_id'],
            name: $validated['name'],
            code: $validated['code'] ?? null,
            description: $validated['description'] ?? null,
            isActive: $request->boolean('is_active'),
            isPublic: $request->boolean('is_public'),
            supportsPickups: $request->boolean('supports_pickups'),
            supportsReturns: $request->boolean('supports_returns'),
            parentId: isset($validated['parent_id']) ? (int) $validated['parent_id'] : null,
        )->execute();

        return to_route('organization.adminland.location.index', $organization->slug)
            ->with('status', trans('Changes saved'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('location.manage')) {
            abort(403);
        }

        $id = $request->route()->parameter('location');

        try {
            $location = $organization->locations()->findOrFail($id);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        new DestroyLocation(
            user: $request->user(),
            organization: $organization,
            location: $location,
        )->execute();

        return to_route('organization.adminland.location.index', $organization->slug)
            ->with('status', trans('Changes saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'branch_id' => ['required', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * @return array{0: Collection<int, Branch>, 1: Collection<int, object>}
     */
    private function indexViewData(Organization $organization): array
    {
        $branches = $organization->branches()->orderBy('name')->get();

        $rootLocations = $organization->locations()
            ->with('children.children.children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $locationsByBranch = $branches->map(fn ($branch) => (object) [
            'id' => $branch->id,
            'name' => $branch->name,
            'locations' => $rootLocations
                ->where('branch_id', $branch->id)
                ->values()
                ->map(fn ($location) => $this->mapLocationForView($location, $organization)),
        ]);

        return [$branches, $locationsByBranch];
    }

    private function mapLocationForView(Location $location, Organization $organization): object
    {
        return (object) [
            'id' => $location->id,
            'name' => $location->name,
            'code' => $location->code,
            'is_active' => $location->is_active,
            'edit_link' => route('organization.adminland.location.edit', [
                'slug' => $organization->slug,
                'location' => $location->id,
            ]),
            'destroy_link' => route('organization.adminland.location.destroy', [
                'slug' => $organization->slug,
                'location' => $location->id,
            ]),
            'children' => $location->children->map(fn ($child) => $this->mapLocationForView($child, $organization)),
        ];
    }

    /**
     * @return array{0: Collection<int, Branch>, 1: Collection<int, Location>, 2: array<string, string>, 3: array<string, string>}
     */
    private function formViewData(Organization $organization, ?Location $excludeLocation): array
    {
        $branches = $organization->branches()->orderBy('name')->get();

        $branchOptions = $branches
            ->mapWithKeys(fn ($b) => [(string) $b->id => $b->name])
            ->all();

        $locationsQuery = $organization->locations()
            ->with('branch')
            ->orderBy('name');

        if ($excludeLocation !== null) {
            $locationsQuery->where('id', '!=', $excludeLocation->id);
        }

        $parentLocations = $locationsQuery->get();

        $parentLocationOptions = ['' => '-'] + $parentLocations
            ->mapWithKeys(fn ($l) => [(string) $l->id => $l->branch->name.' › '.$l->name])
            ->all();

        return [$branches, $parentLocations, $branchOptions, $parentLocationOptions];
    }
}
