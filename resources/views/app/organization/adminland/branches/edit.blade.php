@php
  $countryOptions = ['' => '-'] + $countries->mapWithKeys(fn ($country) => [(string) $country->id => $country->name])->all();
@endphp

<x-app-layout :organization="$organization">
  <x-slot:title>
    {{ __('Edit branch') }}
  </x-slot>

  <x-breadcrumb :items="[
    ['label' => __('Dashboard'), 'route' => route('organization.show', $organization)],
    ['label' => __('Adminland'), 'route' => route('organization.adminland.index', $organization)],
    ['label' => __('Branches'), 'route' => route('organization.adminland.branch.index', $organization->slug)],
    ['label' => $branch->name]
  ]" />

  <!-- settings layout -->
  <div class="grid grow bg-gray-50 sm:grid-cols-[220px_1fr] dark:bg-gray-950">
    <!-- Sidebar -->
    @include('app.organization.adminland._sidebar')

    <!-- Main content -->
    <section class="p-4 sm:p-8">
      <div class="mx-auto max-w-5xl space-y-6 sm:px-0">

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $branch->name }}</h2>

        <form action="{{ route('organization.adminland.branch.update', [$organization->slug, $branch->id]) }}" method="post" class="space-y-8">
          @csrf
          @method('PUT')

          {{-- Section 1: Branch info --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Branch info') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('The name and short code used to identify this branch across the system.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <div class="grid gap-4 sm:grid-cols-2">
                <x-input id="name" :label="__('Name')" type="text" name="name" required autofocus :error="$errors->get('name')" value="{{ old('name', $branch->name) }}" />
                <x-input id="code" :label="__('Code')" type="text" name="code" :error="$errors->get('code')" value="{{ old('code', $branch->code) }}" />
              </div>
            </div>
          </div>

          <hr class="border-gray-200 dark:border-gray-700">

          {{-- Section 2: Address --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Address') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('The physical location of this branch. Used for display and shipping purposes.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <div class="space-y-4">
                <x-input id="address_line_1" :label="__('Address line 1')" type="text" name="address_line_1" required :error="$errors->get('address_line_1')" value="{{ old('address_line_1', $branch->address_line_1) }}" />
                <x-input id="address_line_2" :label="__('Address line 2')" type="text" name="address_line_2" :error="$errors->get('address_line_2')" value="{{ old('address_line_2', $branch->address_line_2) }}" />
                <div class="grid gap-4 sm:grid-cols-2">
                  <x-input id="city" :label="__('City')" type="text" name="city" required :error="$errors->get('city')" value="{{ old('city', $branch->city) }}" />
                  <x-input id="state_province" :label="__('State / Province')" type="text" name="state_province" :error="$errors->get('state_province')" value="{{ old('state_province', $branch->state_province) }}" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                  <x-input id="postal_code" :label="__('Postal code')" type="text" name="postal_code" :error="$errors->get('postal_code')" value="{{ old('postal_code', $branch->postal_code) }}" />
                  <x-select id="country_id" :label="__('Country')" :options="$countryOptions" :selected="old('country_id', $branch->country_id === null ? '' : (string) $branch->country_id)" :error="$errors->get('country_id')" />
                </div>
              </div>
            </div>
          </div>

          <hr class="border-gray-200 dark:border-gray-700">

          {{-- Section 3: Details --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Details') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Optional settings to further describe this branch, including its timezone and a short description.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <div class="grid gap-4 sm:grid-cols-2">
                <x-input id="timezone" :label="__('Timezone')" type="text" name="timezone" :error="$errors->get('timezone')" value="{{ old('timezone', $branch->timezone) }}" />
                <x-input id="description" :label="__('Description')" type="text" name="description" :error="$errors->get('description')" value="{{ old('description', $branch->description) }}" />
              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="flex justify-between">
            <x-button.secondary href="{{ route('organization.adminland.branch.index', $organization->slug) }}">
              {{ __('Cancel') }}
            </x-button.secondary>

            <x-button>
              {{ __('Update') }}
            </x-button>
          </div>
        </form>

      </div>
    </section>
  </div>
</x-app-layout>
