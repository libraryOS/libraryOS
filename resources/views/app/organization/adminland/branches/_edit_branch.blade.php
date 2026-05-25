@php
  $countryOptions = ['' => '-'] + $countries->mapWithKeys(fn ($country) => [(string) $country->id => $country->name])->all();
@endphp

<x-form id="branch-{{ $branch->id }}" x-target="branch-list notifications branch-{{ $branch->id }}" x-target.back="branch-{{ $branch->id }}" action="{{ route('organization.adminland.branch.update', [$organization->slug, $branch->id]) }}" method="post" class="space-y-5 rounded-t-lg border-b border-gray-200 p-4 hover:bg-blue-50 dark:border-gray-700 dark:hover:bg-gray-800">
  @csrf
  @method('PUT')

  <div class="grid gap-4 sm:grid-cols-2">
    <x-input id="name" :label="__('Name')" type="text" name="name" required autofocus :error="$errors->get('name')" value="{{ old('name', $branch->name) }}" />
    <x-input id="code" :label="__('Code')" type="text" name="code" :error="$errors->get('code')" value="{{ old('code', $branch->code) }}" />
  </div>

  <div>
    <x-input id="address_line_1" :label="__('Address line 1')" type="text" name="address_line_1" required :error="$errors->get('address_line_1')" value="{{ old('address_line_1', $branch->address_line_1) }}" />
  </div>

  <div>
    <x-input id="address_line_2" :label="__('Address line 2')" type="text" name="address_line_2" :error="$errors->get('address_line_2')" value="{{ old('address_line_2', $branch->address_line_2) }}" />
  </div>

  <div class="grid gap-4 sm:grid-cols-4">
    <x-input id="city" :label="__('City')" type="text" name="city" required :error="$errors->get('city')" value="{{ old('city', $branch->city) }}" />
    <x-input id="state_province" :label="__('State / Province')" type="text" name="state_province" :error="$errors->get('state_province')" value="{{ old('state_province', $branch->state_province) }}" />
    <x-input id="postal_code" :label="__('Postal code')" type="text" name="postal_code" :error="$errors->get('postal_code')" value="{{ old('postal_code', $branch->postal_code) }}" />
    <x-select id="country_id" :label="__('Country')" :options="$countryOptions" :selected="old('country_id', $branch->country_id === null ? '' : (string) $branch->country_id)" :error="$errors->get('country_id')" />
  </div>

  <div class="grid gap-4 sm:grid-cols-2">
    <x-input id="timezone" :label="__('Timezone')" type="text" name="timezone" :error="$errors->get('timezone')" value="{{ old('timezone', $branch->timezone) }}" />
    <x-input id="description" :label="__('Description')" type="text" name="description" :error="$errors->get('description')" value="{{ old('description', $branch->description) }}" />
  </div>

  <div class="flex justify-between">
    <x-button.secondary href="{{ route('organization.adminland.branch.index', $organization->slug) }}" turbo="true" x-target="branch-{{ $branch->id }}">
      {{ __('Cancel') }}
    </x-button.secondary>

    <x-button class="mr-2">
      {{ __('Update') }}
    </x-button>
  </div>
</x-form>
