<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Organization\Adminland;

use App\Actions\CreatePatronType;
use App\Actions\DestroyPatronType;
use App\Actions\UpdatePatronType;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PatronTypeController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('patron_type.manage')) {
            abort(403);
        }

        return view('app.organization.adminland.patron-types.index', [
            'patronTypes' => $this->getPatronTypes($organization),
        ]);
    }

    public function create(Request $request): View
    {
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('patron_type.manage')) {
            abort(403);
        }

        return view('app.organization.adminland.patron-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('patron_type.manage')) {
            abort(403);
        }

        $validated = $this->validateRequest($request);

        new CreatePatronType(
            user: $request->user(),
            organization: $organization,
            key: $validated['key'],
            name: $validated['name'],
            description: $validated['description'] ?? null,
            isActive: $request->boolean('is_active', true),
            membershipDurationDays: isset($validated['membership_duration_days']) ? (int) $validated['membership_duration_days'] : null,
            maxLoans: isset($validated['max_loans']) ? (int) $validated['max_loans'] : null,
            keepLoanHistory: $request->boolean('keep_loan_history'),
            canReceiveNotifications: $request->boolean('can_receive_notifications', true),
            minimumAge: isset($validated['minimum_age']) ? (int) $validated['minimum_age'] : null,
            maximumAge: isset($validated['maximum_age']) ? (int) $validated['maximum_age'] : null,
        )->execute();

        return to_route('organization.adminland.patron-type.index', $organization->slug)
            ->with('status', trans('Changes saved'));
    }

    public function edit(Request $request): View
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('patron_type.manage')) {
            abort(403);
        }

        $id = $request->route()->parameter('patron_type');

        try {
            $patronType = $organization->patronTypes()->findOrFail($id);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        return view('app.organization.adminland.patron-types.edit', [
            'patronType' => $patronType,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('patron_type.manage')) {
            abort(403);
        }

        $id = $request->route()->parameter('patron_type');

        try {
            $patronType = $organization->patronTypes()->findOrFail($id);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        $validated = $this->validateRequest($request);

        new UpdatePatronType(
            user: $request->user(),
            organization: $organization,
            patronType: $patronType,
            key: $validated['key'],
            name: $validated['name'],
            description: $validated['description'] ?? null,
            isActive: $request->boolean('is_active', true),
            membershipDurationDays: isset($validated['membership_duration_days']) ? (int) $validated['membership_duration_days'] : null,
            maxLoans: isset($validated['max_loans']) ? (int) $validated['max_loans'] : null,
            keepLoanHistory: $request->boolean('keep_loan_history'),
            canReceiveNotifications: $request->boolean('can_receive_notifications', true),
            minimumAge: isset($validated['minimum_age']) ? (int) $validated['minimum_age'] : null,
            maximumAge: isset($validated['maximum_age']) ? (int) $validated['maximum_age'] : null,
        )->execute();

        return to_route('organization.adminland.patron-type.index', $organization->slug)
            ->with('status', trans('Changes saved'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('patron_type.manage')) {
            abort(403);
        }

        $id = $request->route()->parameter('patron_type');

        try {
            $patronType = $organization->patronTypes()->findOrFail($id);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        new DestroyPatronType(
            user: $request->user(),
            organization: $organization,
            patronType: $patronType,
        )->execute();

        return to_route('organization.adminland.patron-type.index', $organization->slug)
            ->with('status', trans('Changes saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'membership_duration_days' => ['nullable', 'integer', 'min:1'],
            'max_loans' => ['nullable', 'integer', 'min:1'],
            'minimum_age' => ['nullable', 'integer', 'min:0'],
            'maximum_age' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function getPatronTypes(Organization $organization): Collection
    {
        return $organization->patronTypes()
            ->orderBy('name')
            ->get()
            ->map(fn ($patronType) => (object) [
                'id' => $patronType->id,
                'name' => $patronType->name,
                'key' => $patronType->key,
                'description' => $patronType->description,
                'is_active' => $patronType->is_active,
                'edit_link' => route('organization.adminland.patron-type.edit', [
                    'slug' => $organization->slug,
                    'patron_type' => $patronType->id,
                ]),
                'destroy_link' => route('organization.adminland.patron-type.destroy', [
                    'slug' => $organization->slug,
                    'patron_type' => $patronType->id,
                ]),
            ]);
    }
}
