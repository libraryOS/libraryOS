<x-app-layout :organization="$organization">
  <x-slot:title>
    {{ __('Roles') }}
  </x-slot>

  <x-breadcrumb :items="[
    ['label' => __('Dashboard'), 'route' => route('organization.show', $organization)],
    ['label' => __('Adminland'), 'route' => route('organization.adminland.index', $organization)],
    ['label' => __('Roles')]
  ]" />

  <!-- settings layout -->
  <div class="grid grow bg-gray-50 sm:grid-cols-[220px_1fr] dark:bg-gray-950">
    <!-- Sidebar -->
    @include('app.organization.adminland._sidebar')

    <!-- Main content -->
    <section class="p-4 sm:p-8">
      <div class="mx-auto max-w-5xl space-y-6 sm:px-0">
        <x-box padding="p-0">
          <x-slot:title>{{ __('All the roles') }}</x-slot>

          @forelse ($roles as $role)
            <div x-data="{ expanded: false }" class="group border-b border-gray-200 last:border-b-0 dark:border-gray-700">
              <div class="flex items-center justify-between p-3 text-sm first:rounded-t-lg last:rounded-b-lg hover:bg-blue-50 dark:hover:bg-gray-800">
                <div class="flex min-w-0 items-center gap-3">
                  <x-phosphor-shield-check class="size-4 min-w-4 text-zinc-600 dark:text-zinc-400" />
                  <div>
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $role->name }}</p>
                    @if ($role->description)
                      <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $role->description }}</p>
                    @endif
                  </div>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ trans_choice(':count member|:count members', $role->members_count) }}
                  </span>

                  <button
                    @click="expanded = !expanded"
                    class="invisible cursor-pointer rounded-md border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-600 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:outline-none group-hover:visible dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                  >
                    <span x-text="expanded ? '{{ __('Hide details') }}' : '{{ __('Show details') }}'"></span>
                  </button>
                </div>
              </div>

              <div x-cloak x-show="expanded" x-transition class="border-t border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/50">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Permissions') }}</p>

                @if ($role->permissions->isEmpty())
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No permissions assigned to this role yet.') }}</p>
                @else
                  <ul class="space-y-1">
                    @foreach ($role->permissions as $permission)
                      <li class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <x-phosphor-check-circle class="size-4 text-emerald-600" />
                        {{ $permission->getName() }}
                      </li>
                    @endforeach
                  </ul>
                @endif
              </div>
            </div>
          @empty
            <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
              {{ __('No roles have been created yet.') }}
            </div>
          @endforelse
        </x-box>
      </div>
    </section>
  </div>
</x-app-layout>
