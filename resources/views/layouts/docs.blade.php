<x-marketing-layout>
  @if (! empty($breadcrumbItems))
    <x-breadcrumb :items="$breadcrumbItems" />
  @endif

  <main layout="marketing-main-layout">
    <div layout="marketing-doc" data-sidebar="{{ isset($rightSidebar) ? 'three-column' : 'two-column' }}">
      <!-- Sidebar -->
      <div layout="marketing-sidebar">
        @php
          $currentVersion = request()->route('version') ?? config('docs.default_version');
          $productNav = require resource_path("views/marketing/docs/{$currentVersion}/nav.php");
          $apiNav = require resource_path("views/marketing/docs/{$currentVersion}/api-nav.php");
        @endphp

        @if (request()->route('version'))
          <div class="version-selector">
            <p>Version</p>
            <ul>
              @foreach (config('docs.versions') as $v)
                <li>
                  <a href="{{ route( request()->route()->getName(),['version' => $v],) }}">
                    {{ $v }}
                  </a>
                </li>
              @endforeach
            </ul>
          </div>
        @endif

        <x-marketing.docs-nav label="Product documentation" :nav="$productNav" :version="$currentVersion" />
        <x-marketing.docs-nav label="API documentation" :nav="$apiNav" :version="$currentVersion" />
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
  </main>
</x-marketing-layout>
