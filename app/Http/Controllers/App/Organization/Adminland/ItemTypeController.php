<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Organization\Adminland;

use App\Actions\CreateItemType;
use App\Actions\DestroyItemType;
use App\Actions\UpdateItemType;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ItemTypeController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('item_type.manage')) {
            abort(403);
        }

        return view('app.organization.adminland.item-types.index', [
            'itemTypes' => $this->getItemTypes($organization),
        ]);
    }

    public function create(Request $request): View
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('item_type.manage')) {
            abort(403);
        }

        return view('app.organization.adminland.item-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('item_type.manage')) {
            abort(403);
        }

        $validated = $this->validateRequest($request);

        new CreateItemType(
            user: $request->user(),
            organization: $organization,
            key: $validated['key'],
            name: $validated['name'] ?? null,
            description: $validated['description'] ?? null,
            isLoanable: $request->boolean('is_loanable'),
            isHoldable: $request->boolean('is_holdable'),
            isVisibleInCatalog: $request->boolean('is_visible_in_catalog'),
            defaultLoanDays: isset($validated['default_loan_days']) ? (int) $validated['default_loan_days'] : null,
        )->execute();

        return to_route('organization.adminland.item-type.index', $organization->slug)
            ->with('status', trans('Changes saved'));
    }

    public function edit(Request $request): View
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('item_type.manage')) {
            abort(403);
        }

        $id = $request->route()->parameter('item_type');

        try {
            $itemType = $organization->itemTypes()->findOrFail($id);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        return view('app.organization.adminland.item-types.edit', [
            'itemType' => $itemType,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('item_type.manage')) {
            abort(403);
        }

        $id = $request->route()->parameter('item_type');

        try {
            $itemType = $organization->itemTypes()->findOrFail($id);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        $validated = $this->validateRequest($request);

        new UpdateItemType(
            user: $request->user(),
            organization: $organization,
            itemType: $itemType,
            key: $validated['key'],
            name: $validated['name'] ?? null,
            description: $validated['description'] ?? null,
            isLoanable: $request->boolean('is_loanable'),
            isHoldable: $request->boolean('is_holdable'),
            isVisibleInCatalog: $request->boolean('is_visible_in_catalog'),
            defaultLoanDays: isset($validated['default_loan_days']) ? (int) $validated['default_loan_days'] : null,
        )->execute();

        return to_route('organization.adminland.item-type.index', $organization->slug)
            ->with('status', trans('Changes saved'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('item_type.manage')) {
            abort(403);
        }

        $id = $request->route()->parameter('item_type');

        try {
            $itemType = $organization->itemTypes()->findOrFail($id);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        new DestroyItemType(
            user: $request->user(),
            organization: $organization,
            itemType: $itemType,
        )->execute();

        return to_route('organization.adminland.item-type.index', $organization->slug)
            ->with('status', trans('Changes saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'default_loan_days' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function getItemTypes(Organization $organization): Collection
    {
        return $organization->itemTypes()
            ->orderBy('name')
            ->get()
            ->map(fn ($itemType) => (object) [
                'id' => $itemType->id,
                'name' => $itemType->getName(),
                'key' => $itemType->key,
                'description' => $itemType->description,
                'is_loanable' => $itemType->is_loanable,
                'is_holdable' => $itemType->is_holdable,
                'is_visible_in_catalog' => $itemType->is_visible_in_catalog,
                'edit_link' => route('organization.adminland.item-type.edit', [
                    'slug' => $organization->slug,
                    'item_type' => $itemType->id,
                ]),
                'destroy_link' => route('organization.adminland.item-type.destroy', [
                    'slug' => $organization->slug,
                    'item_type' => $itemType->id,
                ]),
            ]);
    }
}
