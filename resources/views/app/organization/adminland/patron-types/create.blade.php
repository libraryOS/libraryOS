<x-app-layout :organization="$organization">
  <x-slot:title>
    {{ __('New patron type') }}
  </x-slot>

  <x-breadcrumb :items="[
    ['label' => __('Dashboard'), 'route' => route('organization.show', $organization)],
    ['label' => __('Adminland'), 'route' => route('organization.adminland.index', $organization)],
    ['label' => __('Patron types'), 'route' => route('organization.adminland.patron-type.index', $organization->slug)],
    ['label' => __('New patron type')]
  ]" />

  <!-- settings layout -->
  <div class="grid grow bg-gray-50 sm:grid-cols-[220px_1fr] dark:bg-gray-950">
    <!-- Sidebar -->
    @include('app.organization.adminland._sidebar')

    <!-- Main content -->
    <section class="p-4 sm:p-8">
      <div class="mx-auto max-w-5xl space-y-6 sm:px-0">

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('New patron type') }}</h2>

        <form action="{{ route('organization.adminland.patron-type.store', $organization->slug) }}" method="post" class="space-y-8">
          @csrf

          {{-- Section 1: Patron type info --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Patron type info') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('The name and unique key used to identify this patron type across the system.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <div class="grid gap-4 sm:grid-cols-2">
                <x-input id="name" :label="__('Name')" type="text" name="name" required autofocus :error="$errors->get('name')" value="{{ old('name') }}" />
                <x-input id="key" :label="__('Key')" type="text" name="key" required :error="$errors->get('key')" value="{{ old('key') }}" />
              </div>
            </div>
          </div>

          <hr class="border-gray-200 dark:border-gray-700">

          {{-- Section 2: Membership settings --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Membership settings') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Control the privileges and constraints for patrons of this type.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <div class="flex h-6 items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', true)) class="h-4 w-4 rounded border-gray-300 accent-emerald-600 dark:border-gray-600">
                  </div>
                  <div class="text-sm">
                    <label for="is_active" class="font-medium text-gray-900 dark:text-white">{{ __('Active') }}</label>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Patrons can be assigned this type when it is active.') }}</p>
                  </div>
                </div>

                <div class="grid gap-4 pt-2 sm:grid-cols-2">
                  <x-input id="membership_duration_days" :label="__('Membership duration (days)')" type="number" name="membership_duration_days" :error="$errors->get('membership_duration_days')" value="{{ old('membership_duration_days') }}" />
                  <x-input id="max_loans" :label="__('Max simultaneous loans')" type="number" name="max_loans" :error="$errors->get('max_loans')" value="{{ old('max_loans') }}" />
                  <x-input id="minimum_age" :label="__('Minimum age')" type="number" name="minimum_age" :error="$errors->get('minimum_age')" value="{{ old('minimum_age') }}" />
                  <x-input id="maximum_age" :label="__('Maximum age')" type="number" name="maximum_age" :error="$errors->get('maximum_age')" value="{{ old('maximum_age') }}" />
                </div>
              </div>
            </div>
          </div>

          <hr class="border-gray-200 dark:border-gray-700">

          {{-- Section 3: Preferences --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Preferences') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Configure privacy and notification behaviour for patrons of this type.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <div class="flex h-6 items-center">
                    <input type="hidden" name="keep_loan_history" value="0">
                    <input id="keep_loan_history" name="keep_loan_history" type="checkbox" value="1" @checked(old('keep_loan_history', false)) class="h-4 w-4 rounded border-gray-300 accent-emerald-600 dark:border-gray-600">
                  </div>
                  <div class="text-sm">
                    <label for="keep_loan_history" class="font-medium text-gray-900 dark:text-white">{{ __('Keep loan history') }}</label>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Loan history is retained after items are returned for patrons of this type.') }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="flex h-6 items-center">
                    <input type="hidden" name="can_receive_notifications" value="0">
                    <input id="can_receive_notifications" name="can_receive_notifications" type="checkbox" value="1" @checked(old('can_receive_notifications', true)) class="h-4 w-4 rounded border-gray-300 accent-emerald-600 dark:border-gray-600">
                  </div>
                  <div class="text-sm">
                    <label for="can_receive_notifications" class="font-medium text-gray-900 dark:text-white">{{ __('Can receive notifications') }}</label>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Patrons of this type can receive email and system notifications.') }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <hr class="border-gray-200 dark:border-gray-700">

          {{-- Section 4: Details --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Details') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('An optional description to further explain what this patron type represents.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <x-input id="description" :label="__('Description')" type="text" name="description" :error="$errors->get('description')" value="{{ old('description') }}" />
            </div>
          </div>

          {{-- Actions --}}
          <div class="flex justify-between">
            <x-button.secondary href="{{ route('organization.adminland.patron-type.index', $organization->slug) }}">
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
