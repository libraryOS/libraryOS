<x-app-layout :organization="$organization">
  <x-slot:title>
    {{ __('Edit item type') }}
  </x-slot>

  <x-breadcrumb :items="[
    ['label' => __('Dashboard'), 'route' => route('organization.show', $organization)],
    ['label' => __('Adminland'), 'route' => route('organization.adminland.index', $organization)],
    ['label' => __('Item types'), 'route' => route('organization.adminland.item-type.index', $organization->slug)],
    ['label' => $itemType->getName()]
  ]" />

  <!-- settings layout -->
  <div class="grid grow bg-gray-50 sm:grid-cols-[220px_1fr] dark:bg-gray-950">
    <!-- Sidebar -->
    @include('app.organization.adminland._sidebar')

    <!-- Main content -->
    <section class="p-4 sm:p-8">
      <div class="mx-auto max-w-5xl space-y-6 sm:px-0">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $itemType->getName() }}</h2>

        <form action="{{ route('organization.adminland.item-type.update', [$organization->slug, $itemType->id]) }}" method="post" class="space-y-8">
          @csrf
          @method('PUT')

          {{-- Section 1: Item type info --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Item type info') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('The name and unique key used to identify this item type across the system.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <div class="grid gap-4 sm:grid-cols-2">
                <x-input id="name" :label="__('Name')" type="text" name="name" required autofocus :error="$errors->get('name')" value="{{ old('name', $itemType->name) }}" />
                <x-input id="key" :label="__('Key')" type="text" name="key" required :error="$errors->get('key')" value="{{ old('key', $itemType->key) }}" />
              </div>
            </div>
          </div>

          <hr class="border-gray-200 dark:border-gray-700" />

          {{-- Section 2: Circulation settings --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Circulation settings') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Control how items of this type behave in the circulation workflow.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <div class="flex h-6 items-center">
                    <input type="hidden" name="is_loanable" value="0" />
                    <input id="is_loanable" name="is_loanable" type="checkbox" value="1" @checked(old('is_loanable', $itemType->is_loanable)) class="h-4 w-4 rounded border-gray-300 accent-emerald-600 dark:border-gray-600" />
                  </div>
                  <div class="text-sm">
                    <label for="is_loanable" class="font-medium text-gray-900 dark:text-white">{{ __('Loanable') }}</label>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Items of this type can be checked out to patrons.') }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="flex h-6 items-center">
                    <input type="hidden" name="is_holdable" value="0" />
                    <input id="is_holdable" name="is_holdable" type="checkbox" value="1" @checked(old('is_holdable', $itemType->is_holdable)) class="h-4 w-4 rounded border-gray-300 accent-emerald-600 dark:border-gray-600" />
                  </div>
                  <div class="text-sm">
                    <label for="is_holdable" class="font-medium text-gray-900 dark:text-white">{{ __('Holdable') }}</label>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Patrons can place holds on items of this type.') }}</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <div class="flex h-6 items-center">
                    <input type="hidden" name="is_visible_in_catalog" value="0" />
                    <input id="is_visible_in_catalog" name="is_visible_in_catalog" type="checkbox" value="1" @checked(old('is_visible_in_catalog', $itemType->is_visible_in_catalog)) class="h-4 w-4 rounded border-gray-300 accent-emerald-600 dark:border-gray-600" />
                  </div>
                  <div class="text-sm">
                    <label for="is_visible_in_catalog" class="font-medium text-gray-900 dark:text-white">{{ __('Visible in catalog') }}</label>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('Items of this type will appear in the public catalog.') }}</p>
                  </div>
                </div>

                <div class="pt-2">
                  <x-input id="default_loan_days" :label="__('Default loan days')" type="number" name="default_loan_days" :error="$errors->get('default_loan_days')" value="{{ old('default_loan_days', $itemType->default_loan_days) }}" />
                </div>
              </div>
            </div>
          </div>

          <hr class="border-gray-200 dark:border-gray-700" />

          {{-- Section 3: Details --}}
          <div class="grid gap-6 sm:grid-cols-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('Details') }}</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('An optional description to further explain what this item type represents.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-6 sm:col-span-2 dark:border-gray-700 dark:bg-gray-900">
              <x-input id="description" :label="__('Description')" type="text" name="description" :error="$errors->get('description')" value="{{ old('description', $itemType->description) }}" />
            </div>
          </div>

          {{-- Actions --}}
          <div class="flex justify-between">
            <x-button.secondary href="{{ route('organization.adminland.item-type.index', $organization->slug) }}">
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
