<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Organization\Adminland;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->attributes->get('organization');

        $roles = $organization->roles()
            ->withCount('members')
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn ($role) => (object) [
                'id' => $role->id,
                'name' => $role->getName(),
                'description' => $role->description,
                'is_system' => $role->is_system,
                'members_count' => $role->members_count,
                'permissions' => $role->permissions,
            ]);

        return view('app.organization.adminland.roles.index', [
            'roles' => $roles,
        ]);
    }
}
