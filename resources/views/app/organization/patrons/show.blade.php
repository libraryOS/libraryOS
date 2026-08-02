<x-app-layout :organization="$organization">
  <x-slot:title>
  {{ __('Patron profile') }}
</x-slot>

  <x-breadcrumb :items="[
    ['label' => __('Dashboard'), 'route' => route('organization.show', $organization)],
    ['label' => __('Patron profile')]
  ]" />

  <div class="grid gap-4 lg:grid-cols-2 px-4 py-4">

    <x-box padding="p-0">
      <!-- name -->
      <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Jane Doe</h2>
        <div class="grid grid-cols-1 sm:grid-cols-6 gap-4">
          <div>
            <p class="text-xs text-gray-500">Card #</p>
            <p class=" text-gray-900 font-mono">324311</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Patron type</p>
            <p class=" text-gray-900">Adult</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Status</p>
            <p class=" text-gray-900">Active</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Member since</p>
            <p class=" text-gray-900">Active</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Expiration</p>
            <p class=" text-gray-900">Active</p>
          </div>
          <div>
            <p class="text-xs text-gray-500">Home branch</p>
            <p class=" text-gray-900">Active</p>
          </div>
        </div>
      </div>

      <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
          <x-button.secondary href="{{ route('organization.create') }}" turbo="true">
            <x-slot:icon>
              <x-phosphor-arrow-circle-right class="size-4" />
            </x-slot>
            {{ __('Check out') }}
          </x-button>
          <x-button.secondary href="{{ route('organization.create') }}" turbo="true">
            <x-slot:icon>
              <x-phosphor-arrow-circle-left class="size-4" />
            </x-slot>
            {{ __('Check in') }}
          </x-button>
          <x-button.secondary href="{{ route('organization.create') }}" turbo="true">
            <x-slot:icon>
              <x-phosphor-bookmark class="size-4" />
            </x-slot>
            {{ __('Place hold') }}
          </x-button>
          <x-button.secondary href="{{ route('organization.create') }}" turbo="true">
            <x-slot:icon>
              <x-phosphor-pencil-simple class="size-4" />
            </x-slot>
            {{ __('Edit') }}
          </x-button>
        </div>
      </div>

      <div class="">
        <dl>
          <x-patron-profile-row label="Email">
              <x-slot name="icon"><x-phosphor-envelope class="w-5 h-5" /></x-slot>
              <p>Work: <a href="#" class="text-blue-600">dennis@email.com</a></p>
              <p>Home: <a href="#" class="text-blue-600">dennis@email.com</a></p>
          </x-patron-profile-row>

          <x-patron-profile-row label="Phone Number">
              <x-slot name="icon"><x-phosphor-phone class="w-5 h-5" /></x-slot>
              <p><span class="inline-block w-16 text-gray-500">Mobile:</span> +(555) 203 923</p>
              <p><span class="inline-block w-16 text-gray-500">Work:</span> +(555) 323 232</p>
          </x-patron-profile-row>

          <x-patron-profile-row label="Mailing Address">
              <x-slot name="icon"><x-phosphor-map-pin class="w-5 h-5" /></x-slot>
              <p>134 Baker Street</p>
              <p>San Diego, CA 92093</p>
              <p>USA</p>
          </x-patron-profile-row>

          <x-patron-profile-row label="Internal Notes">
              <x-slot name="icon"><x-phosphor-note class="w-5 h-5" /></x-slot>
              <p class="text-gray-400 italic">No notes yet.</p>
          </x-patron-profile-row>
        </dl>
      </div>
    </x-box>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
      <div class="mb-4 border-b border-gray-200 pb-4 dark:border-gray-700">
        <div class="flex items-center gap-2">
          <button type="button" class="rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700">Activity</button>
          <button type="button" class="rounded-md px-3 py-1.5 text-sm font-semibold text-gray-500">Loans</button>
        </div>
      </div>

      <ol class="space-y-4">
        <li class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
          <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Returned “The Midnight Library”</p>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Due date met · May 24, 2026 at 3:42 PM</p>
        </li>
        <li class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
          <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Checked out “Project Hail Mary”</p>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Loan period: 21 days · May 18, 2026 at 11:10 AM</p>
        </li>
        <li class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
          <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Paid partial overdue balance</p>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Payment: $5.00 card payment · May 8, 2026 at 5:02 PM</p>
        </li>
      </ol>
    </section>
  </div>
</x-app-layout>
