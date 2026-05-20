<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;

class DocsController extends Controller
{
    protected function renderDoc(string $version, string $markdownPath, array $breadcrumbs): View
    {
        $content = Str::of(
            file_get_contents(resource_path("views/marketing/docs/{$version}/{$markdownPath}.md"))
        )->markdown(
            [
                'html_input' => 'strip',
                'heading_permalink' => [
                    'id_prefix' => '',
                    'fragment_prefix' => '',
                    'symbol' => '#',
                    'insert' => 'after',
                    'html_class' => 'heading-anchor',
                ],
            ],
            [new HeadingPermalinkExtension],
        );

        return view('marketing.docs.markdown', compact('content', 'breadcrumbs'));
    }
}
