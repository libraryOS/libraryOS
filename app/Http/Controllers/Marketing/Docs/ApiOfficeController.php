<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing\Docs;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ApiOfficeController extends Controller
{
    public function index(string $version): View
    {
        return view()->file(resource_path("views/marketing/docs/{$version}/api/organizations/offices/index.blade.php"));
    }
}
