<x-app-layout :organization="$organization">
  <x-slot:title>
    {{ __('New location') }}
  </x-slot>

  <x-breadcrumb :items="[
    ['label' => __('Dashboard'), 'route' => route('organization.show', $organization)],
    ['label' => __('Adminland'), 'route' => route('organization.adminland.index', $organization)],
    ['label' => __('Locations'), 'route' => route('organization.adminland.location.index', $organization->slug)],
    ['label' => __('New location')]
  ]" />

  <!-- settings layout -->
  <div class="grid grow bg-gray-50 sm:grid-cols-[220px_1fr] dark:bg-gray-950">
    <!-- Sidebar -->
    @include('app.organization.adminland._sidebar')

    <!-- Main content -->
    <section class="p-4 sm:p-8">
      <div class="mx-auto max-w-5xl space-y-6 sm:px-0">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('New location') }}</h2>

        <form action="{{ route('organization.adminland.location.store', $organization->slug) }}" method="post" class="space-y-8">
          @csrf

          {{-- Section 1: Location info --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Location info') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('The name and optional short code used to identify this location across the system.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <div class="grid gap-4 sm:grid-cols-2">
                <x-input id="name" :label="__('Name')" type="text" name="name" required autofocus :error="$errors->get('name')" value="{{ old('name') }}" />
                <x-input id="code" :label="__('Code')" type="text" name="code" :error="$errors->get('code')" value="{{ old('code') }}" />
              </div>
              <div class="mt-4">
                <x-input id="description" :label="__('Description')" type="text" name="description" :error="$errors->get('description')" value="{{ old('description') }}" />
              </div>
            </div>
          </div>

          <hr class="border-gray-200 dark:border-gray-700" />

          {{-- Section 2: Branch & hierarchy --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Branch & hierarchy') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Choose which branch this location belongs to. Optionally assign a parent location to build a nested structure, such as a shelf inside a section.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <div class="space-y-4">
                <x-select id="branch_id" :label="__('Branch')" name="branch_id" :options="$branchOptions" :selected="old('branch_id')" :error="$errors->get('branch_id')" />
                <x-select id="parent_id" :label="__('Parent location')" name="parent_id" :options="$parentLocationOptions" :selected="old('parent_id', '')" :error="$errors->get('parent_id')" />
              </div>
            </div>
          </div>

          <hr class="border-gray-200 dark:border-gray-700" />

          {{-- Section 3: Settings --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Settings') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Control the availability and circulation behavior of this location.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <div class="flex h-6 items-center">
                    <input type="hidden" name="is_active" value="0" />
                    <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', true)) class="h-4 w-4 rounded border-gray-300 accent-emerald-600 dark:border-gray-600" />
                  </div>
                  <div class="text-sm">
                    <label for="is_active" class="font-medium text-gray-900 dark:text-white">{{ __('Active') }}</label>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Location is active and available for use in the system.') }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="flex h-6 items-center">
                    <input type="hidden" name="is_public" value="0" />
                    <input id="is_public" name="is_public" type="checkbox" value="1" @checked(old('is_public', true)) class="h-4 w-4 rounded border-gray-300 accent-emerald-600 dark:border-gray-600" />
                  </div>
                  <div class="text-sm">
                    <label for="is_public" class="font-medium text-gray-900 dark:text-white">{{ __('Public') }}</label>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Location is visible to patrons in the public catalog.') }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="flex h-6 items-center">
                    <input type="hidden" name="supports_pickups" value="0" />
                    <input id="supports_pickups" name="supports_pickups" type="checkbox" value="1" @checked(old('supports_pickups', false)) class="h-4 w-4 rounded border-gray-300 accent-emerald-600 dark:border-gray-600" />
                  </div>
                  <div class="text-sm">
                    <label for="supports_pickups" class="font-medium text-gray-900 dark:text-white">{{ __('Supports pickups') }}</label>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Items can be designated for pickup at this location.') }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="flex h-6 items-center">
                    <input type="hidden" name="supports_returns" value="0" />
                    <input id="supports_returns" name="supports_returns" type="checkbox" value="1" @checked(old('supports_returns', false)) class="h-4 w-4 rounded border-gray-300 accent-emerald-600 dark:border-gray-600" />
                  </div>
                  <div class="text-sm">
                    <label for="supports_returns" class="font-medium text-gray-900 dark:text-white">{{ __('Supports returns') }}</label>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Items can be returned at this location.') }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="flex justify-between">
            <x-button.secondary href="{{ route('organization.adminland.location.index', $organization->slug) }}">
              {{ __('Cancel') }}
            </x-button.secondary>

            <x-button>
              {{ __('Create') }}
            </x-button>
          </div>
        </form>
      </div>
    </section>
  </div>
</x-app-layout>
