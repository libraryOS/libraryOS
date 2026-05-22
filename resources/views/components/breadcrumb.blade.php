@props([
  'items',
])

<nav layout="marketing-breadcrumb-nav" aria-label="Breadcrumb">
  <span>{{ __('You are here:') }}</span>

  <ol>
    @foreach ($items as $item)
      <li>
        @if (isset($item['route']))
          <x-link href="{{ $item['route'] }}">{{ $item['label'] }}</x-link>
        @else
          <span aria-current="page">{{ $item['label'] }}</span>
        @endif
      </li>
    @endforeach
  </ol>
</nav>
