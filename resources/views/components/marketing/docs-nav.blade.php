@props([
    'label',
    'nav',
    'version' => null,
    'rootOpen' => false,
])

@php
    $isActive = static fn (array $item): bool => request()->routeIs(...($item['active_routes'] ?? []));

    $anyActive = static function (array $items) use (&$anyActive, $isActive): bool {
        foreach ($items as $item) {
            if ($isActive($item)) {
                return true;
            }

            if (isset($item['children']) && $anyActive($item['children'])) {
                return true;
            }
        }

        return false;
    };

    $url = static function (array $item) use ($version): string {
        $params = [];

        if (($item['versioned'] ?? false) && $version !== null) {
            $params['version'] = $version;
        }

        return route($item['route'], $params);
    };

    $levelStyles = [
        0 => [
            'group' => 'mb-3 flex flex-col gap-y-2',
            'title' => 'mb-3 flex cursor-pointer items-center justify-between rounded-md border border-transparent px-2 py-1 pl-3 text-xs text-gray-500 uppercase hover:border-gray-200 hover:bg-blue-50 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:bg-gray-800',
            'linkPadding' => 'pl-6',
            'heading' => 'mt-2 pl-6 text-xs font-semibold tracking-widest text-gray-400 uppercase dark:text-gray-500',
        ],
        1 => [
            'group' => 'flex flex-col gap-y-2',
            'title' => 'flex cursor-pointer items-center justify-between rounded-md border border-transparent px-2 py-1 pl-6 text-xs text-gray-500 uppercase hover:border-gray-200 hover:bg-blue-50 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:bg-gray-800',
            'linkPadding' => 'pl-9',
            'heading' => 'mt-2 pl-9 text-xs font-semibold tracking-widest text-gray-400 uppercase dark:text-gray-500',
        ],
    ];

    $defaultLevelStyle = [
        'group' => 'flex flex-col gap-y-2',
        'title' => 'flex cursor-pointer items-center justify-between rounded-md border border-transparent px-2 py-1 pl-9 text-xs text-gray-500 uppercase hover:border-gray-200 hover:bg-blue-50 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:bg-gray-800',
        'linkPadding' => 'pl-12',
        'heading' => 'mt-2 pl-12 text-xs font-semibold tracking-widest text-gray-400 uppercase dark:text-gray-500',
    ];

    $renderItems = static function (array $items, int $level = 0) use (&$renderItems, $anyActive, $isActive, $url, $levelStyles, $defaultLevelStyle): string {
        $style = $levelStyles[$level] ?? $defaultLevelStyle;

        $html = '<div class="'.$style['group'].'">';

        foreach ($items as $item) {
            if (isset($item['children'])) {
                $isOpen = $anyActive($item['children']) ? 'true' : 'false';
                $childContent = $renderItems($item['children'], $level + 1);
                $heading = $level >= 1 && (bool) ($item['heading'] ?? false);
                $wrapperTag = $heading ? 'div' : 'li';
                $labelTag = $heading ? 'p' : 'h3';
                $titleClass = $heading ? $style['heading'] : $style['title'];

                $html .= '<'.$wrapperTag.' x-data="{ open: '.$isOpen.' }">';
                $html .= '<div @click.stop="open = !open" class="'.$titleClass.'">';
                $html .= '<'.$labelTag.'>'.e($item['label']).'</'.$labelTag.'>';

                if (! $heading) {
                    $html .= '<x-phosphor-caret-right x-bind:class="open ? \'rotate-90\' : \'\'" class="h-4 w-4 text-gray-500 transition-transform duration-300" />';
                }

                $html .= '</div>';
                $html .= '<div x-show="open" class="'.($level >= 1 ? '' : 'mb-3 ').'"'.($level === 0 ? ' x-cloak' : '').'>'.$childContent.'</div>';
                $html .= '</'.$wrapperTag.'>';
            } else {
                $activeClass = $isActive($item) ? 'border-l-blue-400' : 'border-l-transparent';
                $html .= '<div><a href="'.$url($item).'" class="'.$activeClass.' block border-l-3 '.$style['linkPadding'].' hover:border-l-blue-400 hover:underline">'.e($item['label']).'</a></div>';
            }
        }

        $html .= '</div>';

        return $html;
    };
@endphp

<div x-data="{ open: {{ $rootOpen || $anyActive($nav) ? 'true' : 'false' }} }">
    <div @click="open = !open" class="mb-2 flex cursor-pointer items-center justify-between rounded-md border border-transparent px-2 py-1 hover:border-gray-200 hover:bg-blue-50 dark:hover:border-gray-700 dark:hover:bg-gray-800">
        <h3>{{ $label }}</h3>
        <x-phosphor-caret-right x-bind:class="open ? 'rotate-90' : ''" class="h-4 w-4 text-gray-500 transition-transform duration-300" />
    </div>

    <div x-show="open" x-cloak class="mb-10 ml-3">
        {!! $renderItems($nav) !!}
    </div>
</div>
