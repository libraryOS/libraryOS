<x-app-layout :organization="$organization">
  <x-slot:title>
    {{ __('Patron profile') }}
  </x-slot>

  <x-breadcrumb :items="[
    ['label' => __('Dashboard'), 'route' => route('organization.index')],
    ['label' => __('Organization'), 'route' => route('organization.show', $organization->slug)],
    ['label' => __('Patrons'), 'route' => route('organization.patron.show', [$organization->slug, 'P-1001'])],
    ['label' => __('Patron #').$patronId],
  ]" />

  <div class="grid gap-6 lg:grid-cols-2">
    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
      <div class="mb-6 flex items-start gap-4 border-b border-gray-200 pb-6 dark:border-gray-700">
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-xl font-semibold text-indigo-700">AL</div>
        <div class="space-y-1">
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Amelia Lopez</h1>
          <p class="text-sm text-gray-600 dark:text-gray-300">Patron ID: {{ $patronId }} · Active Member</p>
          <p class="text-sm text-gray-600 dark:text-gray-300">Registered: January 14, 2024</p>
        </div>
      </div>

      <dl class="grid gap-y-5 text-sm sm:grid-cols-2 sm:gap-x-6">
        <div><dt class="text-gray-500">Email</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">amelia.lopez@example.test</dd></div>
        <div><dt class="text-gray-500">Phone</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">(555) 014-2219</dd></div>
        <div><dt class="text-gray-500">Membership Type</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">Standard Adult</dd></div>
        <div><dt class="text-gray-500">Preferred Branch</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">Riverview Branch</dd></div>
        <div class="sm:col-span-2"><dt class="text-gray-500">Mailing Address</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">1448 Willow Creek Ave, Portland, OR 97205</dd></div>
        <div><dt class="text-gray-500">Account Balance</dt><dd class="mt-1 text-amber-700 dark:text-amber-400">$4.50 overdue fees</dd></div>
        <div><dt class="text-gray-500">Communication</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">Email + SMS enabled</dd></div>
      </dl>
    </section>

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
