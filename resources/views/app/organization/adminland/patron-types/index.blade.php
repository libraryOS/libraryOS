<x-app-layout :organization="$organization">
  <x-slot:title>
    {{ __('Patron types') }}
  </x-slot>

  <x-breadcrumb :items="[
    ['label' => __('Dashboard'), 'route' => route('organization.show', $organization)],
    ['label' => __('Adminland'), 'route' => route('organization.adminland.index', $organization)],
    ['label' => __('Patron types')]
  ]" />

  <!-- settings layout -->
  <div class="grid grow bg-gray-50 sm:grid-cols-[220px_1fr] dark:bg-gray-950">
    <!-- Sidebar -->
    @include('app.organization.adminland._sidebar')

    <!-- Main content -->
    <section class="p-4 sm:p-8">
      <div class="mx-auto max-w-5xl space-y-6 sm:px-0">
        @include(
          'app.organization.adminland.patron-types._patron_types',
          [
            'patronTypes' => $patronTypes,
          ]
        )
      </div>
    </section>
  </div>
</x-app-layout>
