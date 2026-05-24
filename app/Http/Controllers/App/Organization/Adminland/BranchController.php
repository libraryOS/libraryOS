<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Organization\Adminland;

use App\Actions\CreateBranch;
use App\Actions\DestroyBranch;
use App\Actions\UpdateBranch;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Organization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->attributes->get('organization');

        [$branches, $countries] = $this->viewData($organization);

        return view('app.organization.adminland.branches.index', [
            'branches' => $branches,
            'countries' => $countries,
        ]);
    }

    public function create(Request $request): View
    {
        $organization = $request->attributes->get('organization');
        [, $countries] = $this->viewData($organization);

        return view('app.organization.adminland.branches._create_branch', [
            'countries' => $countries,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $validated = $this->validateRequest($request);

        new CreateBranch(
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

        return to_route('organization.adminland.branch.index', $organization->slug)
            ->with('status', trans('Changes saved'));
    }

    public function edit(Request $request): View
    {
        $organization = $request->attributes->get('organization');
        $id = $request->route()->parameter('branch');

        try {
            $branch = $organization->branches()->findOrFail($id);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        [, $countries] = $this->viewData($organization);

        return view('app.organization.adminland.branches._edit_branch', [
            'branch' => $branch,
            'countries' => $countries,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $id = $request->route()->parameter('branch');

        try {
            $branch = $organization->branches()->findOrFail($id);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        $validated = $this->validateRequest($request);

        new UpdateBranch(
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

        return to_route('organization.adminland.branch.index', $organization->slug)
            ->with('status', trans('Changes saved'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $organization = $request->attributes->get('organization');
        $id = $request->route()->parameter('branch');

        try {
            $branch = $organization->branches()->findOrFail($id);
        } catch (ModelNotFoundException) {
            abort(404);
        }

        new DestroyBranch(
            user: $request->user(),
            organization: $organization,
            branch: $branch,
        )->execute();

        return to_route('organization.adminland.branch.index', $organization->slug)
            ->with('status', trans('Changes saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'office_type_id' => ['nullable', 'integer'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state_province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'integer'],
            'timezone' => ['nullable', 'string', 'max:50'],
        ]);
    }

    /**
     * @return array{0: mixed, 1: mixed}
     */
    private function viewData(Organization $organization): array
    {
        $branches = $organization->branches()
            ->with('country')
            ->orderBy('name')
            ->get()
            ->map(fn ($branch) => (object) [
                'id' => $branch->id,
                'name' => $branch->name,
                'address' => (string) $branch->address,
                'edit_link' => route('organization.adminland.branch.edit', [
                    'slug' => $organization->slug,
                    'branch' => $branch->id,
                ]),
                'destroy_link' => route('organization.adminland.branch.destroy', [
                    'slug' => $organization->slug,
                    'branch' => $branch->id,
                ]),
            ]);

        $countries = Country::query()
            ->orderBy('name')
            ->get()
            ->map(fn ($country) => (object) [
                'id' => $country->id,
                'name' => $country->name,
            ]);

        return [$branches, $countries];
    }
}
