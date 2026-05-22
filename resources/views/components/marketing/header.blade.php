<header>
  <!-- main nav -->
  <nav layout="marketing-nav">
    <!-- Logo -->
    <a href="" variant="marketing-logo" data-turbo="true">
      <div>
        <x-image src="{{ asset('images/marketing/logo/30x30.webp') }}" srcset="{{ asset('images/marketing/logo/30x30.webp') }} 1x, {{ asset('images/marketing/logo/30x30@2x.webp') }} 2x" width="25" height="25" alt="{{ config('app.name') }} logo" />
      </div>
      <span>{{ config('app.name') }}</span>
    </a>

    <!-- Main navigation - centered (hidden on mobile) -->
    <div layout="marketing-nav-links">
      <div>
        <a href="" data-turbo="true" data-active="{{ str_starts_with( request()->route()->getName(),'marketing.docs.',) ? 'true' : 'false' }}">
          <x-phosphor-squares-four variant="modules" />
          <span>Modules</span>
        </a>

        <a href="" data-turbo="true" data-active="{{ str_starts_with( request()->route()->getName(),'marketing.pricing.',) ? 'true' : 'false' }}">
          <x-phosphor-credit-card variant="pricing" />
          <span>Pricing</span>
        </a>

        <a href="{{ route('marketing.docs.index') }}" data-turbo="true" data-active="{{ str_starts_with( request()->route()->getName(),'marketing.docs.',) ? 'true' : 'false' }}">
          <x-phosphor-book-open variant="docs" />
          <span>Docs</span>
        </a>

        <a href="" data-turbo="true" data-active="{{ str_starts_with( request()->route()->getName(),'marketing.company.',) ? 'true' : 'false' }}">
          <x-phosphor-building variant="company" />
          <span>Company</span>
        </a>

        <a href="https://github.com/djaiss/libraryOS">
          <x-phosphor-github-logo variant="github" />
          <span>Github</span>
        </a>
      </div>
    </div>

    <!-- Right side - user menu -->
    @if (Auth::check())
      <div class="relative ms-3 flex items-center gap-x-3">
        <a href="{{ route('organization.index') }}" data-turbo="true" class="group flex items-center gap-x-2 rounded-sm border border-b-3 border-gray-400 px-2 py-1 text-sm transition-colors duration-150 hover:bg-white dark:border-slate-500 dark:text-slate-100 dark:hover:bg-gray-700/60">
          <x-phosphor-door class="h-4 w-4 text-gray-500" />
          Go to your account
        </a>
      </div>
    @else
      <div layout="marketing-nav-login">
        <a href="{{ route('login') }}" data-turbo="true">Sign in</a>
        <a href="{{ route('register') }}" data-turbo="true">Get started</a>
      </div>
    @endif
  </nav>
</header>
