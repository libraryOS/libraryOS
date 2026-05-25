<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganization
{
    /**
     * Check if the user is a member of the organization.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route()->parameter('slug');
        $id = (int) Str::before($slug, '-');

        try {
            $organization = Organization::query()->findOrFail($id);

            $member = $request->user()->memberOf($organization);
            abort_unless($member !== null, 403);

            $request->attributes->add(['organization' => $organization]);
            $request->attributes->add(['member' => $member]);
            $request->attributes->add(['permissions' => $member->getPermissions()]);

            View::share('organization', $organization);
            View::share('member', $member);
            View::share('permissions', $member->getPermissions());

            return $next($request);
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }
}
