@props(['label', 'nav', 'version' => null])

@php
$isActive = fn (array $item): bool => request()->routeIs(...$item['active_routes']);

$anyActive = function (array $items) use (&$anyActive, $isActive): bool {
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

$url = function (array $item) use ($version): string {
  $params = ($item['versioned'] ?? false) ? ['version' => $version] : [];

  return route($item['route'], $params);
};
@endphp

<div x-data="{ open: {{ $anyActive($nav) ? 'true' : 'false' }} }">
  <div @click="open = !open" class="doc-section">
    <h3>{{ $label }}</h3>
    <x-phosphor-caret-right x-bind:data-open="open ? 'true' : 'false'" />
  </div>

  <ul x-show="open" x-cloak class="doc-section-content">
    @foreach ($nav as $item)
      @if (isset($item['children']))
      <li x-data="{ open: {{ $anyActive($item['children']) ? 'true' : 'false' }} }">
        <div @click.stop="open = !open" class="doc-section">
          <h3>{{ $item['label'] }}</h3>
          <x-phosphor-caret-right x-bind:data-open="open ? 'true' : 'false'" />
        </div>

        <ul x-show="open" class="doc-section-content">
          @foreach ($item['children'] as $child)
            @if (isset($child['children']))
            <li x-data="{ open: {{ $anyActive($child['children']) ? 'true' : 'false' }} }">
              <div @click.stop="open = !open" class="doc-section">
                <h3>{{ $child['label'] }}</h3>
                <x-phosphor-caret-right x-bind:data-open="open ? 'true' : 'false'" />
              </div>

              <ul x-show="open" class="doc-section-content">
                @foreach ($child['children'] as $grandchild)
                <li>
                  <a href="{{ $url($grandchild) }}" data-active="{{ $isActive($grandchild) ? 'true' : 'false' }}" data-turbo="true">
                    {{ $grandchild['label'] }}
                  </a>
                </li>
                @endforeach
              </ul>
            </li>
            @else
            <li>
              <a href="{{ $url($child) }}" data-active="{{ $isActive($child) ? 'true' : 'false' }}" data-turbo="true">
                {{ $child['label'] }}
              </a>
            </li>
            @endif
          @endforeach
        </ul>
      </li>
      @else
      <li>
        <a href="{{ $url($item) }}" data-active="{{ $isActive($item) ? 'true' : 'false' }}" data-turbo="true">
          {{ $item['label'] }}
        </a>
      </li>
      @endif
    @endforeach
  </ul>
</div>
