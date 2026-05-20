<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing\Docs;

use Illuminate\View\View;

class AdminlandDepartmentController extends DocsController
{
    public function index(string $version): View
    {
        return $this->renderDoc($version, 'features/departments/index', [
            ['label' => 'Home', 'route' => route('marketing.index')],
            ['label' => 'Documentation', 'route' => route('marketing.docs.index')],
            ['label' => 'Departments'],
        ]);
    }
}
