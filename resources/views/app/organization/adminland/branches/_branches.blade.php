<x-box padding="p-0">
  <x-slot:title>{{ __('Branches') }}</x-slot>

  <x-slot:description>
    <p>{{ __('A branch represents a public service point / lending location. You can manage as many branches as needed to cover your organization\'s needs.') }}</p>
  </x-slot>

  <x-slot:actions>
    <div class="flex items-center gap-x-2">
      <x-button.secondary href="{{ route('organization.adminland.branch.create', $organization->slug) }}">
        {{ __('Add') }}
      </x-button.secondary>
    </div>
  </x-slot>

  <div id="branch-list">
    @forelse ($branches as $branch)
      <div id="branch-{{ $branch->id }}" class="group flex items-center justify-between border-b border-gray-200 p-3 first:rounded-t-lg last:rounded-b-lg last:border-b-0 hover:bg-blue-50 dark:border-gray-700 dark:hover:bg-gray-800">
        <div class="flex items-start justify-between gap-3">
          <div class="rounded-sm bg-zinc-100 p-2 group-hover:bg-zinc-200 dark:bg-zinc-700 dark:group-hover:bg-zinc-600">
            <x-phosphor-map-pin class="h-4 w-4 text-zinc-500" />
          </div>

          <div class="flex flex-col">
            <p class="text-sm font-semibold">{{ $branch->name }}</p>
            <p class="text-xs text-zinc-500">{{ $branch->branch_type ?? '-' }}</p>
            <p class="text-xs text-zinc-500">{{ $branch->address }}</p>
          </div>
        </div>

        <div class="flex gap-2">
          <x-button.invisible x-target="branch-{{ $branch->id }}" href="{{ $branch->edit_link }}" class="invisible text-sm group-hover:visible">
            {{ __('Edit') }}
          </x-button.invisible>

          <form x-target="branch-list" x-on:ajax:before="
            confirm('Are you sure you want to proceed? This can not be undone.') ||
              $event.preventDefault()
          " action="{{ $branch->destroy_link }}" method="POST">
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
          <x-phosphor-map-pin class="h-6 w-6" />
        </x-slot>

        {{ __('No branches found. Start by adding your first branch.') }}
      </x-empty-state>
    @endforelse
  </div>
</x-box>
