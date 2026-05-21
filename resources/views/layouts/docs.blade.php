<x-marketing-layout>
  @if (! empty($breadcrumbItems))
    <x-breadcrumb :items="$breadcrumbItems" />
  @endif

  <main layout="marketing-main-layout">
    <div layout="marketing-doc" data-sidebar="{{ isset($rightSidebar) ? 'three-column' : 'two-column' }}">
      <!-- Sidebar -->
      <div layout="marketing-sidebar">
        <div
          x-data="{
            productDocumentation:
              '{{ request()->routeIs('marketing.docs.index') || request()->routeIs('marketing.docs.organizations.*') || request()->routeIs('marketing.docs.offices.*') || request()->routeIs('marketing.docs.departments.*') ? 'true' : 'false' }}' ===
              'true',
            manageYourOrganizationDocumentation:
              '{{ request()->routeIs('marketing.docs.organizations.*') || request()->routeIs('marketing.docs.offices.*') || request()->routeIs('marketing.docs.departments.*') ? 'true' : 'false' }}' ===
              'true',
            manageOfficesDocumentation:
              '{{ request()->routeIs('marketing.docs.offices.*') ? 'true' : 'false' }}' ===
              'true',
            manageDepartmentsDocumentation:
              '{{ request()->routeIs('marketing.docs.departments.*') ? 'true' : 'false' }}' ===
              'true',
            openApiDocumentation:
              '{{ str_starts_with( request()->route()->getName(),'marketing.docs.api.',) ? 'true' : 'false' }}' ===
              'true',
            organizationsDocumentation:
              '{{ str_starts_with( request()->route()->getName(),'marketing.docs.api.organizations.',) ? 'true' : 'false' }}' ===
              'true',
            officeTypesDocumentation:
              '{{ request()->routeIs('marketing.docs.api.organizations.officetypes.*') ? 'true' : 'false' }}' ===
              'true',
            officesDocumentation:
              '{{ request()->routeIs('marketing.docs.api.organizations.offices.*') ? 'true' : 'false' }}' ===
              'true',
            membersDocumentation:
              '{{ request()->routeIs('marketing.docs.api.organizations.members.*') ? 'true' : 'false' }}' ===
              'true',
            memberTypesDocumentation:
              '{{ request()->routeIs('marketing.docs.api.organizations.membertypes.*') ? 'true' : 'false' }}' ===
              'true',
            departmentsDocumentation:
              '{{ request()->routeIs('marketing.docs.api.organizations.departments.*') ? 'true' : 'false' }}' ===
              'true',
          }">

          @if (request()->route('version'))
            <div class="version-selector">
              <p>Version</p>
              <ul>
                @foreach (config('docs.versions') as $v)
                  <li>
                    <a href="{{ route(request()->route()->getName(), ['version' => $v]) }}">
                      {{ $v }}
                    </a>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif

          <!-- product documentation -->
          <div @click="productDocumentation = !productDocumentation" class="doc-section">
            <h3>Product documentation</h3>
            <x-phosphor-caret-right x-bind:data-open="productDocumentation ? 'true' : 'false'" />
          </div>

          <ul x-show="productDocumentation" x-cloak class="doc-section-content">
            <li>
              <a href="{{ route('marketing.docs.index') }}" data-turbo="true">Introduction</a>
            </li>

            <!-- manage your organization -->
            <div @click.stop="manageYourOrganizationDocumentation = !manageYourOrganizationDocumentation" class="doc-section">
              <h3>Manage your organization</h3>
              <x-phosphor-caret-right x-bind:data-open="manageYourOrganizationDocumentation ? 'true' : 'false'" />
            </div>
            <ul x-show="manageYourOrganizationDocumentation" class="doc-section-content">
              {{-- getting started --}}
              <div>
                <a href="{{ route('marketing.docs.organizations.index', ['version' => request()->route('version') ?? config('docs.default_version')]) }}" data-turbo="true">Getting started</a>
              </div>

              {{-- manage offices --}}
              <p class="subsection-title">Manage offices</p>
              <li>
                <a href="{{ route('marketing.docs.offices.index', ['version' => request()->route('version') ?? config('docs.default_version')]) }}">Getting started</a>
              </li>
              <li>
                <a href="{{ route('marketing.docs.offices.manage', ['version' => request()->route('version') ?? config('docs.default_version')]) }}">Manage offices</a>
              </li>

              {{-- manage departments --}}
              <p class="subsection-title">Manage departments</p>
              <li>
                <a href="{{ route('marketing.docs.departments.index', ['version' => request()->route('version') ?? config('docs.default_version')]) }}">Getting started</a>
              </li>
              <li>
                <a href="{{ route('marketing.docs.departments.manage', ['version' => request()->route('version') ?? config('docs.default_version')]) }}">Manage departments</a>
              </li>
            </ul>
          </ul>

          <!-- api documentation -->
          <div @click="openApiDocumentation = !openApiDocumentation" class="doc-section">
            <h3>API documentation</h3>
            <x-phosphor-caret-right x-bind:data-open="openApiDocumentation ? 'true' : 'false'" />
          </div>

          <div x-show="openApiDocumentation" x-cloak class="doc-section-content">
            <li>
              <a href="{{ route('marketing.docs.api.index') }}">Introduction</a>
            </li>

            <!-- organizations -->
            <div @click="organizationsDocumentation = !organizationsDocumentation" class="doc-section">
              <h3>Organizations</h3>
              <x-phosphor-caret-right x-bind:data-open="organizationsDocumentation ? 'true' : 'false'" />
            </div>
            <ul x-show="organizationsDocumentation" class="doc-section-content">
              <li>
                <a href="{{ route('marketing.docs.api.organizations.index') }}">Organizations</a>
              </li>

              <!-- adminland (api) -->
              <div @click.stop="officeTypesDocumentation = !officeTypesDocumentation; officesDocumentation = !officesDocumentation; membersDocumentation = !membersDocumentation; memberTypesDocumentation = !memberTypesDocumentation; departmentsDocumentation = !departmentsDocumentation">
                <h3>Adminland</h3>
                <x-phosphor-caret-right x-bind:data-open="officeTypesDocumentation || officesDocumentation || membersDocumentation || memberTypesDocumentation || departmentsDocumentation ? 'true' : 'false'" />
              </div>
              <div x-show="officeTypesDocumentation || officesDocumentation || membersDocumentation || memberTypesDocumentation || departmentsDocumentation" class="flex flex-col gap-y-2">
                <div>
                  <a href="{{ route('marketing.docs.api.organizations.officetypes.index') }}" class="{{ request()->routeIs('marketing.docs.api.organizations.officetypes.index') ? 'border-l-blue-400' : 'border-l-transparent' }} block border-l-3 pl-9 hover:border-l-blue-400 hover:underline">Office Types</a>
                </div>
                <div>
                  <a href="{{ route('marketing.docs.api.organizations.offices.index') }}" class="{{ request()->routeIs('marketing.docs.api.organizations.offices.index') ? 'border-l-blue-400' : 'border-l-transparent' }} block border-l-3 pl-9 hover:border-l-blue-400 hover:underline">Offices</a>
                </div>
                <div>
                  <a href="{{ route('marketing.docs.api.organizations.members.index') }}" class="{{ request()->routeIs('marketing.docs.api.organizations.members.index') ? 'border-l-blue-400' : 'border-l-transparent' }} block border-l-3 pl-9 hover:border-l-blue-400 hover:underline">Members</a>
                </div>
                <div>
                  <a href="{{ route('marketing.docs.api.organizations.membertypes.index') }}" class="{{ request()->routeIs('marketing.docs.api.organizations.membertypes.index') ? 'border-l-blue-400' : 'border-l-transparent' }} block border-l-3 pl-9 hover:border-l-blue-400 hover:underline">Member Types</a>
                </div>
                <div>
                  <a href="{{ route('marketing.docs.api.organizations.departments.index') }}" class="{{ request()->routeIs('marketing.docs.api.organizations.departments.index') ? 'border-l-blue-400' : 'border-l-transparent' }} block border-l-3 pl-9 hover:border-l-blue-400 hover:underline">Departments</a>
                </div>
              </div>
            </ul>
          </div>
        </div>
      </div>

      <!-- Main content -->
      <div>
        {{ $slot }}
      </div>

      <!-- Sidebar -->
      @if ($rightSidebar ?? false)
        <div class="hidden w-full shrink-0 flex-col justify-self-end py-16 sm:border-l sm:border-gray-200 sm:pl-6 lg:flex">
          {{ $rightSidebar ?? '' }}
        </div>
      @endif
    </div>
  </main>
</x-marketing-layout>
