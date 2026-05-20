<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing\Docs;

use Illuminate\View\View;

class AdminlandOfficeManageController extends DocsController
{
    public function index(string $version): View
    {
        return $this->renderDoc($version, 'features/offices/manage', [
            ['label' => 'Home', 'route' => route('marketing.index')],
            ['label' => 'Documentation', 'route' => route('marketing.docs.index')],
            ['label' => 'Manage offices'],
        ]);
    }
}
