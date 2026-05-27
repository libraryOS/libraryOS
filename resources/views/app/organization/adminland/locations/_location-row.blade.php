@php $paddingLeft = ($depth * 24) + 12; @endphp

<div id="location-{{ $location->id }}" class="group flex items-center justify-between border-b border-gray-200 py-3 pr-3 last:border-b-0 hover:bg-blue-50 dark:border-gray-700 dark:hover:bg-gray-800" style="padding-left: {{ $paddingLeft }}px">
  <div class="flex items-center gap-2">
    @if ($depth > 0)
      <x-phosphor-arrow-bend-down-right class="h-3 w-3 shrink-0 text-gray-400 dark:text-gray-500" />
    @endif

    <div class="rounded-sm bg-zinc-100 p-2 group-hover:bg-zinc-200 dark:bg-zinc-700 dark:group-hover:bg-zinc-600">
      <x-phosphor-map-pin class="h-4 w-4 text-zinc-500" />
    </div>

    <div class="flex flex-col">
      <div class="flex items-center gap-2">
        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $location->name }}</span>

        @if ($location->code)
          <span class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-500 dark:bg-gray-700 dark:text-gray-400">{{ $location->code }}</span>
        @endif

        @if (! $location->is_active)
          <span class="rounded bg-amber-100 px-1.5 py-0.5 text-xs text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">{{ __('Inactive') }}</span>
        @endif
      </div>
    </div>
  </div>

  <div class="flex shrink-0 gap-2">
    <x-button.invisible href="{{ $location->edit_link }}" class="invisible text-sm group-hover:visible">
      {{ __('Edit') }}
    </x-button.invisible>

    <form x-target="location-list" x-on:ajax:before="
      confirm('Are you sure you want to proceed? This can not be undone.') ||
        $event.preventDefault()
    " action="{{ $location->destroy_link }}" method="POST">
      @csrf
      @method('DELETE')

      <x-button.invisible class="invisible text-sm group-hover:visible">
        {{ __('Delete') }}
      </x-button.invisible>
    </form>
  </div>
</div>

@foreach ($location->children as $child)
  @include('app.organization.adminland.locations._location-row', ['location' => $child, 'depth' => $depth + 1])
@endforeach
