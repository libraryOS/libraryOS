@props([
  'turbo' => true,
])

<a @if ($turbo) data-turbo="true" @endif {{ $attributes }}>{{ $slot }}</a>
