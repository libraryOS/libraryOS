<aside class="flex-col border-b border-gray-200 bg-white px-4 py-4 sm:flex sm:rounded-bl-lg sm:border-r sm:border-b-0 dark:border-gray-700 dark:bg-gray-900">
  <nav class="flex flex-col gap-4">
    <div class="flex flex-col gap-1">
      <a data-turbo="true" href="{{ route('settings.security.index') }}" class="{{ request()->routeIs('settings.security.index') ? 'bg-gray-100 font-medium text-gray-900 dark:bg-gray-800 dark:text-gray-100' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }} flex items-center gap-2 rounded-lg px-2 py-1">
        <x-phosphor-key class="h-4 w-4 {{ request()->routeIs('settings.security.index') ? 'text-emerald-700' : 'text-gray-500' }}" />
        {{ __('Preferences') }}
      </a>
      <a data-turbo="true" href="" class="{{ request()->routeIs('settings.account.index') ? 'bg-red-50 font-medium text-red-600' : 'text-red-600 hover:bg-red-50' }} flex items-center gap-2 rounded-lg px-2 py-1">
        <x-phosphor-trash class="h-4 w-4" />
        {{ __('Danger zone') }}
      </a>
    </div>
    <div class="flex flex-col gap-1">
      <p class="text-xs font-medium text-gray-500 uppercase">{{ __('Administration') }}</p>
      <a data-turbo="true" href="{{ route('settings.security.index') }}" class="{{ request()->routeIs('settings.security.index') ? 'bg-gray-100 font-medium text-gray-900 dark:bg-gray-800 dark:text-gray-100' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }} flex items-center gap-2 rounded-lg px-2 py-1">
        <x-phosphor-key class="h-4 w-4 {{ request()->routeIs('settings.security.index') ? 'text-emerald-700' : 'text-gray-500' }}" />
        {{ __('Organization') }}
      </a>

      @if ($permissions->contains('role.manage'))
        <a data-turbo="true" href="{{ route('organization.adminland.role.index', $organization) }}" class="{{ request()->routeIs('organization.adminland.role.*') ? 'bg-gray-100 font-medium text-gray-900 dark:bg-gray-800 dark:text-gray-100' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }} flex items-center gap-2 rounded-lg px-2 py-1">
          <x-phosphor-shield-check class="h-4 w-4 {{ request()->routeIs('organization.adminland.role.*') ? 'text-emerald-700' : 'text-gray-500' }}" />
          {{ __('Roles') }}
        </a>
      @endif

      <a data-turbo="true" href="{{ route('organization.adminland.member.index', $organization) }}" class="{{ request()->routeIs('organization.adminland.member.index') ? 'bg-gray-100 font-medium text-gray-900 dark:bg-gray-800 dark:text-gray-100' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }} flex items-center gap-2 rounded-lg px-2 py-1">
        <x-phosphor-users-three class="h-4 w-4 {{ request()->routeIs('organization.adminland.member.index') ? 'text-emerald-700' : 'text-gray-500' }}" />
        {{ __('Members') }}
      </a>

      @if ($permissions->contains('branch.manage'))
        <a data-turbo="true" href="{{ route('organization.adminland.branch.index', $organization) }}" class="{{ request()->routeIs('organization.adminland.branch.*') ? 'bg-gray-100 font-medium text-gray-900 dark:bg-gray-800 dark:text-gray-100' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }} flex items-center gap-2 rounded-lg px-2 py-1">
          <x-phosphor-building-office class="h-4 w-4 {{ request()->routeIs('organization.adminland.branch.*') ? 'text-emerald-700' : 'text-gray-500' }}" />
          {{ __('Branches') }}
        </a>
      @endif

      @if ($permissions->contains('location.manage'))
        <a data-turbo="true" href="{{ route('organization.adminland.location.index', $organization) }}" class="{{ request()->routeIs('organization.adminland.location.*') ? 'bg-gray-100 font-medium text-gray-900 dark:bg-gray-800 dark:text-gray-100' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }} flex items-center gap-2 rounded-lg px-2 py-1">
          <x-phosphor-map-trifold class="h-4 w-4 {{ request()->routeIs('organization.adminland.location.*') ? 'text-emerald-700' : 'text-gray-500' }}" />
          {{ __('Locations') }}
        </a>
      @endif

      @if ($permissions->contains('item_type.manage'))
        <a data-turbo="true" href="{{ route('organization.adminland.item-type.index', $organization) }}" class="{{ request()->routeIs('organization.adminland.item-type.*') ? 'bg-gray-100 font-medium text-gray-900 dark:bg-gray-800 dark:text-gray-100' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }} flex items-center gap-2 rounded-lg px-2 py-1">
          <x-phosphor-disc class="h-4 w-4 {{ request()->routeIs('organization.adminland.item-type.*') ? 'text-emerald-700' : 'text-gray-500' }}" />
          {{ __('Item types') }}
        </a>
      @endif

      @if ($permissions->contains('patron_type.manage'))
        <a data-turbo="true" href="{{ route('organization.adminland.patron-type.index', $organization) }}" class="{{ request()->routeIs('organization.adminland.patron-type.*') ? 'bg-gray-100 font-medium text-gray-900 dark:bg-gray-800 dark:text-gray-100' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }} flex items-center gap-2 rounded-lg px-2 py-1">
          <x-phosphor-users-three class="h-4 w-4 {{ request()->routeIs('organization.adminland.patron-type.*') ? 'text-emerald-700' : 'text-gray-500' }}" />
          {{ __('Patron types') }}
        </a>
      @endif
    </div>
  </nav>
</aside>
