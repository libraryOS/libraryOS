<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Organization;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class PopulateOrganization implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Organization $organization,
    ) {}

    /**
     * Populate the organization with initial data.
     */
    public function handle(): void
    {
        $this->addDefaultRoles();
        $this->addDefaultPermissions();
        $this->mapPermissionsWithRoles();
        $this->addOfficeTypes();
        $this->addMemberTypes();
    }

    private function addDefaultRoles(): void
    {
        $rolesData = [
            [
                'key' => 'owner',
                'name_translation_key' => trans_key('role_owner'),
                'description' => 'Full control over the organization. Can manage settings, staff, roles, domains, billing, and ownership transfer.',
            ],
            [
                'key' => 'administrator',
                'name_translation_key' => trans_key('role_administrator'),
                'description' => 'Operational administrator of the library. Can manage catalog, patrons, circulation, branches, and most settings.',
            ],
        ];

        $this->organization->roles()->createMany($rolesData);
    }

    private function addDefaultPermissions(): void
    {
        $permissionsData = [
            [
                'key' => 'organization.update',
                'name_translation_key' => trans_key('Update organization'),
                'description' => trans_key('Allows the user to update the organization information, such as its name, branding, and general configuration.'),
            ],

            [
                'key' => 'organization.delete',
                'name_translation_key' => trans_key('Delete organization'),
                'description' => trans_key('Allows the user to permanently delete the organization and all associated data.'),
            ],
        ];

        $this->organization->permissions()->createMany($permissionsData);
    }

    private function mapPermissionsWithRoles(): void
    {
        $mapping = [
            'owner' => [
                'organization.update',
                'organization.delete',
            ],
            'administrator' => [
                'organization.update',
            ],
        ];

        $roles = $this->organization->roles()->get()->keyBy('key');
        $permissions = $this->organization->permissions()->get()->keyBy('key');
        $now = now();

        $pivotRows = [];

        foreach ($mapping as $roleKey => $permissionKeys) {
            $role = $roles->get($roleKey);

            if (! $role) {
                continue;
            }

            foreach ($permissionKeys as $permissionKey) {
                $permission = $permissions->get($permissionKey);

                if (! $permission) {
                    continue;
                }

                $pivotRows[] = [
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($pivotRows !== []) {
            DB::table('permission_role')->insert($pivotRows);
        }
    }

    private function addOfficeTypes(): void
    {
        $types = [
            'Headquarters',
            'Office',
            'Remote',
            'Coworking',
            'Other',
        ];

        foreach ($types as $position => $name) {
            $this->organization->officeTypes()->create([
                'name' => $name,
                'position' => $position,
            ]);
        }
    }

    private function addMemberTypes(): void
    {
        $types = [
            'Member',
            'Employee',
            'Student',
            'Freelance',
        ];

        foreach ($types as $position => $name) {
            $this->organization->memberTypes()->create([
                'name' => $name,
                'position' => $position,
            ]);
        }
    }
}
