<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Member;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function createOrganization(string $name = 'New York Public Library'): Organization
    {
        return Organization::factory()->create([
            'name' => $name,
        ]);
    }

    protected function assignUserToOrganization(User $user, Organization $organization, array $permissions = []): Member
    {
        // create role
        if ($permissions !== []) {
            foreach ($permissions as $permission) {
                Permission::query()->firstOrCreate([
                    'organization_id' => $organization->id,
                    'key' => $permission,
                    'name_translation_key' => 'random text',
                ]);
            }

            $role = $organization->roles()->create([
                'key' => fake()->unique()->slug(2),
                'name' => 'Test Role',
            ]);

            $role->permissions()->sync(
                Permission::query()->whereIn('key', $permissions)->pluck('id')->toArray()
            );
        }

        return Member::factory()->create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'role_id' => $role->id ?? null,
        ]);
    }
}
