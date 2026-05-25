<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Organization\Adminland;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminlandController extends Controller
{
    public function index(Request $request): View
    {
        $permissions = $request->attributes->get('permissions');
        if (! $permissions->contains('adminland.access')) {
            abort(403);
        }

        return view('app.organization.adminland.index');
    }
}
