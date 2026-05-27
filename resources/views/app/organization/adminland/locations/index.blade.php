<x-app-layout :organization="$organization">
  <x-slot:title>
    {{ __('Locations') }}
  </x-slot>

  <x-breadcrumb :items="[
    ['label' => __('Dashboard'), 'route' => route('organization.show', $organization)],
    ['label' => __('Adminland'), 'route' => route('organization.adminland.index', $organization)],
    ['label' => __('Locations')]
  ]" />

  <!-- settings layout -->
  <div class="grid grow bg-gray-50 sm:grid-cols-[220px_1fr] dark:bg-gray-950">
    <!-- Sidebar -->
    @include('app.organization.adminland._sidebar')

    <!-- Main content -->
    <section class="p-4 sm:p-8">
      <div class="mx-auto max-w-5xl space-y-6 sm:px-0">
        <x-box padding="p-0">
          <x-slot:title>{{ __('Locations') }}</x-slot>

          <x-slot:description>
            <p>{{ __('Locations represent physical areas within a branch where items are shelved or stored. They can be nested to reflect real-world structures, such as a section containing shelves.') }}</p>
          </x-slot>

          @if ($hasBranches)
            <x-slot:actions>
              <div class="flex items-center gap-x-2">
                <x-button.secondary href="{{ route('organization.adminland.location.create', $organization->slug) }}">
                  {{ __('Add') }}
                </x-button.secondary>
              </div>
            </x-slot>
          @endif

          <div id="location-list">
            @if (! $hasBranches)
              <x-empty-state>
                <x-slot:icon>
                  <x-phosphor-map-trifold class="h-6 w-6" />
                </x-slot>

                {{ __('No branches found. You need to create at least one branch before adding locations.') }}

                <x-slot:action>
                  <x-button.secondary href="{{ route('organization.adminland.branch.index', $organization->slug) }}">
                    {{ __('Manage branches') }}
                  </x-button.secondary>
                </x-slot>
              </x-empty-state>
            @elseif ($locationsByBranch->every(fn ($branch) => $branch->locations->isEmpty()))
              <x-empty-state>
                <x-slot:icon>
                  <x-phosphor-map-trifold class="h-6 w-6" />
                </x-slot>

                {{ __('No locations found. Start by adding your first location.') }}
              </x-empty-state>
            @else
              @foreach ($locationsByBranch as $branch)
                {{-- Branch section header --}}
                <div class="flex items-center gap-2 border-b border-gray-200 bg-gray-50 px-3 py-2 first:rounded-t-lg dark:border-gray-700 dark:bg-gray-800/50">
                  <div class="rounded-sm bg-zinc-200 p-1 dark:bg-zinc-700">
                    <x-phosphor-building-office class="h-3.5 w-3.5 text-zinc-600 dark:text-zinc-400" />
                  </div>
                  <span class="text-xs font-semibold tracking-wide text-gray-600 uppercase dark:text-gray-400">{{ $branch->name }}</span>
                  <span class="text-xs text-gray-400 dark:text-gray-500">({{ $branch->locations->count() }})</span>
                </div>

                @if ($branch->locations->isEmpty())
                  <div class="border-b border-gray-200 px-4 py-3 text-sm text-gray-400 italic last:border-b-0 dark:border-gray-700 dark:text-gray-500">
                    {{ __('No locations yet.') }}
                  </div>
                @else
                  @foreach ($branch->locations as $location)
                    @include('app.organization.adminland.locations._location-row', ['location' => $location, 'depth' => 0])
                  @endforeach
                @endif
              @endforeach
            @endif
          </div>
        </x-box>
      </div>
    </section>
  </div>
</x-app-layout>
