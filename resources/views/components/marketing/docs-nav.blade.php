@props([
  'items' => [],
  'version' => '',
  'depth' => 0,
])

@foreach ($items as $item)
  @if (count($item['children']) > 0)
    @php
      $sectionIsActive = $item['url'] !== null && request()->is("docs/{$version}/{$item['url']}*");
    @endphp
    <div x-data="{ open: {{ $sectionIsActive ? 'true' : 'false' }} }" class="mb-1">
      <div
        @click="open = !open"
        class="mb-1 flex cursor-pointer items-center justify-between rounded-md border border-transparent px-2 py-1 text-xs font-semibold tracking-widest text-gray-500 uppercase hover:border-gray-200 hover:bg-blue-50 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:bg-gray-800"
        style="padding-left: {{ 8 + $depth * 12 }}px">
        @if ($item['url'])
          <a href="/docs/{{ $version }}/{{ $item['url'] }}" @click.stop data-turbo="true">{{ $item['label'] }}</a>
        @else
          <span>{{ $item['label'] }}</span>
        @endif
        <x-phosphor-caret-right x-bind:class="open ? 'rotate-90' : ''" class="h-4 w-4 text-gray-500 transition-transform duration-300" />
      </div>
      <div x-show="open" x-cloak class="mb-2">
        <x-marketing.docs-nav :items="$item['children']" :version="$version" :depth="$depth + 1" />
      </div>
    </div>
  @else
    @php
      $isActive = request()->is("docs/{$version}/{$item['url']}");
    @endphp
    <div class="mb-1">
      <a
        href="/docs/{{ $version }}/{{ $item['url'] }}"
        data-turbo="true"
        class="{{ $isActive ? 'border-l-blue-400' : 'border-l-transparent' }} block border-l-3 hover:border-l-blue-400 hover:underline"
        style="padding-left: {{ 12 + $depth * 12 }}px">
        {{ $item['label'] }}
      </a>
    </div>
  @endif
@endforeach
