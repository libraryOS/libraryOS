<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Contracts\View\View;

class PatronController extends Controller
{
    public function show(Organization $organization, string $patron): View
    {
        return view('app.organization.patrons.show', [
            'patronId' => $patron,
            'organization' => $organization,
        ]);
    }
}
