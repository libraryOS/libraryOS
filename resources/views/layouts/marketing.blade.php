<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @include('partials.meta', ['title' => $title ?? null])

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- json-ld -->
    @yield('json-ld')
  </head>
  <body>
    <div layout="skeleton">
      @include('components.marketing.header')

      <!-- Page Content -->
      <div>
        @if (! empty($breadcrumbItems))
          <x-breadcrumb :items="$breadcrumbItems" />
        @endif

        {{ $slot }}
      </div>

      @include('components.marketing.footer')
    </div>
  </body>
</html>
