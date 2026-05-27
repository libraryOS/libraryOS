<x-app-layout :organization="$organization">
  <x-slot:title>
    {{ __('Item types') }}
  </x-slot>

  <x-breadcrumb :items="[
    ['label' => __('Dashboard'), 'route' => route('organization.show', $organization)],
    ['label' => __('Adminland'), 'route' => route('organization.adminland.index', $organization)],
    ['label' => __('Item types')]
  ]" />

  <!-- settings layout -->
  <div class="grid grow bg-gray-50 sm:grid-cols-[220px_1fr] dark:bg-gray-950">
    <!-- Sidebar -->
    @include('app.organization.adminland._sidebar')

    <!-- Main content -->
    <section class="p-4 sm:p-8">
      <div class="mx-auto max-w-5xl space-y-6 sm:px-0">
        <x-box padding="p-0">
          <x-slot:title>{{ __('Item types') }}</x-slot>

          <x-slot:description>
            <p>{{ __('Item types categorize the materials in your collection and control how they behave in the circulation workflow.') }}</p>
          </x-slot>

          <x-slot:actions>
            <div class="flex items-center gap-x-2">
              <x-button.secondary href="{{ route('organization.adminland.item-type.create', $organization->slug) }}">
                {{ __('Add') }}
              </x-button.secondary>
            </div>
          </x-slot>

          <div id="item-type-list">
            @forelse ($itemTypes as $itemType)
              <div id="item-type-{{ $itemType->id }}" class="group flex items-center justify-between border-b border-gray-200 p-3 first:rounded-t-lg last:rounded-b-lg last:border-b-0 hover:bg-blue-50 dark:border-gray-700 dark:hover:bg-gray-800">
                <div class="flex items-start justify-between gap-3">
                  <div class="rounded-sm bg-zinc-100 p-2 group-hover:bg-zinc-200 dark:bg-zinc-700 dark:group-hover:bg-zinc-600">
                    <x-phosphor-tag class="h-4 w-4 text-zinc-500" />
                  </div>

                  <div class="flex flex-col">
                    <p>
                      <span class="font-semibold">{{ $itemType->name }}</span>
                      <span class="text-zinc-500 text-xs font-mono">{{ $itemType->key }}</span>
                    </p>
                    @if ($itemType->description)
                      <p class="text-xs text-zinc-500">{{ $itemType->description }}</p>
                    @endif
                  </div>
                </div>

                <div class="flex gap-2">
                  <x-button.invisible href="{{ $itemType->edit_link }}" class="invisible text-sm group-hover:visible">
                    {{ __('Edit') }}
                  </x-button.invisible>

                  <form x-target="item-type-list" x-on:ajax:before="
                    confirm('Are you sure you want to proceed? This can not be undone.') ||
                      $event.preventDefault()
                  " action="{{ $itemType->destroy_link }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <x-button.invisible class="invisible text-sm group-hover:visible">
                      {{ __('Delete') }}
                    </x-button.invisible>
                  </form>
                </div>
              </div>
            @empty
              <x-empty-state>
                <x-slot:icon>
                  <x-phosphor-tag class="h-6 w-6" />
                </x-slot>

                {{ __('No item types found. Start by adding your first item type.') }}
              </x-empty-state>
            @endforelse
          </div>
        </x-box>
      </div>
    </section>
  </div>
</x-app-layout>
