<x-marketing-layout>
  @if (! empty($breadcrumbItems))
    <x-breadcrumb :items="$breadcrumbItems" />
  @endif

  <div class="relative mx-auto max-w-7xl px-6 lg:px-8 xl:px-0">
    <div class="grid grid-cols-1 gap-x-16 {{ isset($rightSidebar) ? 'lg:grid-cols-[300px_1fr_250px]' : 'lg:grid-cols-[300px_1fr]' }}">
      <!-- Sidebar -->
      <div class="hidden w-full shrink-0 flex-col justify-self-end sm:border-r sm:border-gray-200 sm:pr-3 lg:flex dark:sm:border-gray-700">
        @php
          $docsVersion = request()->route('version') ?? config('docs.default_version');
          $productNav = [];
          $apiNav = [];

          if ($docsVersion !== null) {
              $productNavPath = resource_path("views/marketing/docs/{$docsVersion}/nav.php");
              $apiNavPath = resource_path("views/marketing/docs/{$docsVersion}/api-nav.php");

              if (file_exists($productNavPath)) {
                  $productNav = require $productNavPath;
              }

              if (file_exists($apiNavPath)) {
                  $apiNav = require $apiNavPath;
              }
          }
        @endphp

        <div class="bg-light dark:bg-dark z-10 pt-16"> 
          @if (request()->route('version'))
            <div class="mb-6">
              <p class="mb-2 text-xs tracking-widest text-gray-400 uppercase dark:text-gray-500">Version</p>
              <div class="flex gap-3">
                @foreach (config('docs.versions') as $v)
                  <a href="{{ route(request()->route()->getName(), ['version' => $v]) }}" class="{{ request()->route('version') === $v ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                    {{ $v }}
                  </a>
                @endforeach
              </div>
            </div>
          @endif

          @if (! empty($productNav))
            <x-marketing.docs-nav label="Product documentation" :nav="$productNav" :version="$docsVersion" :root-open="request()->routeIs('marketing.docs.index') || request()->routeIs('marketing.docs.organizations.*') || request()->routeIs('marketing.docs.offices.*') || request()->routeIs('marketing.docs.departments.*')" />
          @endif

          @if (! empty($apiNav))
            <x-marketing.docs-nav label="API documentation" :nav="$apiNav" :version="$docsVersion" :root-open="str_starts_with(request()->route()?->getName() ?? '', 'marketing.docs.api.')" />
          @endif
        </div>
        </div>
      </div>

      <!-- Main content -->
      <div>
        {{ $slot }}
      </div>

      <!-- Sidebar -->
        @if ($rightSidebar ?? false)
          <div class="hidden w-full shrink-0 flex-col justify-self-end py-16 sm:border-l sm:border-gray-200 sm:pl-6 lg:flex">
            {{ $rightSidebar ?? '' }}
          </div>
        @endif
    </div>
  </div>
</x-marketing-layout>
