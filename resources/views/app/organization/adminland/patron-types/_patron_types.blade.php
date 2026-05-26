<x-box padding="p-0">
  <x-slot:title>{{ __('Patron types') }}</x-slot>

  <x-slot:description>
    <p>{{ __('Patron types define the different categories of library members and control their borrowing privileges.') }}</p>
  </x-slot>

  <x-slot:actions>
    <div class="flex items-center gap-x-2">
      <x-button.secondary href="{{ route('organization.adminland.patron-type.create', $organization->slug) }}">
        {{ __('Add') }}
      </x-button.secondary>
    </div>
  </x-slot>

  <div id="patron-type-list">
    @forelse ($patronTypes as $patronType)
      <div id="patron-type-{{ $patronType->id }}" class="group flex items-center justify-between border-b border-gray-200 p-3 first:rounded-t-lg last:rounded-b-lg last:border-b-0 hover:bg-blue-50 dark:border-gray-700 dark:hover:bg-gray-800">
        <div class="flex items-start justify-between gap-3">
          <div class="rounded-sm bg-zinc-100 p-2 group-hover:bg-zinc-200 dark:bg-zinc-700 dark:group-hover:bg-zinc-600">
            <x-phosphor-identification-card class="h-4 w-4 text-zinc-500" />
          </div>

          <div class="flex flex-col">
            <p>
              <span class="font-semibold">{{ $patronType->name }}</span>
              <span class="font-mono text-xs text-zinc-500">{{ $patronType->key }}</span>
              @if (! $patronType->is_active)
                <span class="ml-1 inline-flex items-center rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">{{ __('Inactive') }}</span>
              @endif
            </p>
            @if ($patronType->description)
              <p class="text-xs text-zinc-500">{{ $patronType->description }}</p>
            @endif
          </div>
        </div>

        <div class="flex gap-2">
          <x-button.invisible href="{{ $patronType->edit_link }}" class="invisible text-sm group-hover:visible">
            {{ __('Edit') }}
          </x-button.invisible>

          <form x-target="patron-type-list" x-on:ajax:before="
            confirm('Are you sure you want to proceed? This can not be undone.') ||
              $event.preventDefault()
          " action="{{ $patronType->destroy_link }}" method="POST">
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
          <x-phosphor-identification-card class="h-6 w-6" />
        </x-slot>

        {{ __('No patron types found. Start by adding your first patron type.') }}
      </x-empty-state>
    @endforelse
  </div>
</x-box>
