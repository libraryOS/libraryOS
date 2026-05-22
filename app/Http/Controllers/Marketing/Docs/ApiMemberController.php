<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ApiMemberController extends Controller
{
    public function index(string $version): View
    {
        return view()->file(resource_path("views/marketing/docs/{$version}/api/organizations/members/index.blade.php"));
    }
}
