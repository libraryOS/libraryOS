@php
  use Illuminate\Support\Facades\File;
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Str;

  $version = request()->route('version') ?? config('docs.default_version');
  $docsRoot = resource_path("views/marketing/docs/{$version}");
  $indexRouteNames = ['marketing.docs.index', 'marketing.docs.api.index'];

  $formatNavLabel = static function (string $name): string {
      return Str::of($name)
          ->replace(['-', '_'], ' ')
          ->title()
          ->replace('Api', 'API')
          ->replace('Oh Dear', 'Oh Dear')
          ->toString();
  };

  $routeLookup = collect(Route::getRoutes()->getRoutesByName())
      ->filter(static fn ($route, string $name) => Str::startsWith($name, 'marketing.docs.'))
      ->mapWithKeys(static fn ($route, string $name) => [$route->uri() => $name]);

  $buildNavTree = function (string $directory, string $pathPrefix = '') use (&$buildNavTree, $docsRoot, $formatNavLabel, $routeLookup): array {
      $items = [];

      foreach (File::directories($directory) as $childDirectory) {
          $childName = basename($childDirectory);
          $relativePath = ltrim($pathPrefix.'/'.$childName, '/');
          $items[] = [
              'type' => 'section',
              'label' => $formatNavLabel($childName),
              'children' => $buildNavTree($childDirectory, $relativePath),
          ];
      }

      foreach (File::files($directory) as $file) {
          if (! in_array($file->getExtension(), ['md', 'php'], true)) {
              continue;
          }

          if ($file->getExtension() === 'php' && ! Str::endsWith($file->getFilename(), '.blade.php')) {
              continue;
          }

          $baseName = $file->getBasename('.'.$file->getExtension());

          if ($file->getExtension() === 'php') {
              $baseName = Str::before($file->getFilename(), '.blade.php');
          }

          if (in_array($baseName, ['nav', 'api-nav'], true)) {
              continue;
          }

          $relativePath = ltrim($pathPrefix.'/'.$baseName, '/');
          $docPath = Str::of($relativePath)->replace('/index', '')->toString();
          $uri = $docPath === '' ? "docs/{version}" : "docs/{version}/{$docPath}";
          $routeName = $routeLookup->get($uri);

          if (! is_string($routeName)) {
              continue;
          }

          $items[] = [
              'type' => 'link',
              'label' => $formatNavLabel($baseName),
              'route' => $routeName,
          ];
      }

      usort($items, static function (array $left, array $right): int {
          if ($left['type'] !== $right['type']) {
              return $left['type'] === 'section' ? -1 : 1;
          }

          return strcmp($left['label'], $right['label']);
      });

      return $items;
  };

  $navigation = [
      [
          'type' => 'section',
          'label' => 'Product documentation',
          'open' => request()->routeIs('marketing.docs.index') || request()->routeIs('marketing.docs.organizations.*') || request()->routeIs('marketing.docs.offices.*') || request()->routeIs('marketing.docs.departments.*'),
          'children' => [
              [
                  'type' => 'link',
                  'label' => 'Introduction',
                  'route' => 'marketing.docs.index',
              ],
              ...$buildNavTree($docsRoot.'/features', 'features'),
          ],
      ],
      [
          'type' => 'section',
          'label' => 'API documentation',
          'open' => request()->routeIs('marketing.docs.api.*'),
          'children' => [
              [
                  'type' => 'link',
                  'label' => 'Introduction',
                  'route' => 'marketing.docs.api.index',
              ],
              ...$buildNavTree($docsRoot.'/api', 'api'),
          ],
      ],
  ];

  $renderNavItem = function (array $item, int $level = 0) use (&$renderNavItem, $version): string {
      $padding = ['pl-3', 'pl-6', 'pl-9', 'pl-12'][$level] ?? 'pl-12';

      if ($item['type'] === 'link') {
          $isActive = request()->routeIs($item['route']);
          $routeParameters = in_array($item['route'], ['marketing.docs.index'], true) ? [] : ['version' => $version];
          $url = route($item['route'], $routeParameters);

          return '<div><a href="'.$url.'" class="'.($isActive ? 'border-l-blue-400' : 'border-l-transparent').' block border-l-3 '.$padding.' hover:border-l-blue-400 hover:underline">'.$item['label'].'</a></div>';
      }

      $hasActiveChild = collect($item['children'])->contains(function (array $child) use (&$renderNavItem): bool {
          if ($child['type'] === 'link') {
              return request()->routeIs($child['route']);
          }

          return collect($child['children'])->contains(function (array $nested): bool {
              if ($nested['type'] === 'link') {
                  return request()->routeIs($nested['route']);
              }

              return false;
          });
      });

      $childrenHtml = collect($item['children'])->map(fn (array $child): string => $renderNavItem($child, $level + 1))->implode('');
      $summaryClasses = $level === 0
          ? 'mb-2 flex cursor-pointer items-center justify-between rounded-md border border-transparent px-2 py-1 hover:border-gray-200 hover:bg-blue-50 dark:hover:border-gray-700 dark:hover:bg-gray-800'
          : 'mb-2 flex cursor-pointer items-center justify-between rounded-md border border-transparent px-2 py-1 '.$padding.' text-xs text-gray-500 uppercase hover:border-gray-200 hover:bg-blue-50 dark:text-gray-400 dark:hover:border-gray-700 dark:hover:bg-gray-800';

      return '<details class="mb-3" '.($hasActiveChild ? 'open' : '').'><summary class="'.$summaryClasses.'"><h3>'.$item['label'].'</h3><span class="text-gray-500 transition-transform duration-300 group-open:rotate-90">›</span></summary><div class="ml-3 flex flex-col gap-y-2">'.$childrenHtml.'</div></details>';
  };
@endphp

<x-marketing-layout>
  @if (! empty($breadcrumbItems))
    <x-breadcrumb :items="$breadcrumbItems" />
  @endif

  <div class="relative mx-auto max-w-7xl px-6 lg:px-8 xl:px-0">
    <div class="grid grid-cols-1 gap-x-16 {{ isset($rightSidebar) ? 'lg:grid-cols-[300px_1fr_250px]' : 'lg:grid-cols-[300px_1fr]' }}">
      <div class="hidden w-full shrink-0 flex-col justify-self-end sm:border-r sm:border-gray-200 sm:pr-3 lg:flex dark:sm:border-gray-700">
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

          @foreach ($navigation as $navItem)
            {!! $renderNavItem($navItem) !!}
          @endforeach
        </div>
      </div>

      <div>
        {{ $slot }}
      </div>

      @if ($rightSidebar ?? false)
        <div class="hidden w-full shrink-0 flex-col justify-self-end py-16 sm:border-l sm:border-gray-200 sm:pl-6 lg:flex">
          {{ $rightSidebar ?? '' }}
        </div>
      @endif
    </div>
  </div>
</x-marketing-layout>
