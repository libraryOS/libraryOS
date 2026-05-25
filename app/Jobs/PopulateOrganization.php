<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\PermissionEnum;
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
        $this->assignFirstUserAsOwner();
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
                'key' => 'adminland.access',
                'name_translation_key' => trans_key('Access adminland'),
                'description' => trans_key('Allows the user to access the adminland section of the organization.'),
            ],
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
            [
                'key' => PermissionEnum::RoleManage->value,
                'name_translation_key' => trans_key('Manage roles'),
                'description' => trans_key('Allows the user to manage role settings.'),
            ],
            [
                'key' => PermissionEnum::BranchManage->value,
                'name_translation_key' => trans_key('Manage branches'),
                'description' => trans_key('Allows the user to manage branches settings and configurations.'),
            ],
        ];

        $this->organization->permissions()->createMany($permissionsData);
    }

    private function mapPermissionsWithRoles(): void
    {
        $mapping = [
            'owner' => [
                PermissionEnum::AdminlandAccess->value,
                PermissionEnum::OrganizationUpdate->value,
                PermissionEnum::OrganizationDelete->value,
                PermissionEnum::RoleManage->value,
                PermissionEnum::BranchManage->value,
            ],
            'administrator' => [
                PermissionEnum::AdminlandAccess->value,
                PermissionEnum::OrganizationUpdate->value,
                PermissionEnum::RoleManage->value,
                PermissionEnum::BranchManage->value,
            ],
        ];

        $roles = $this->organization->roles()->get()->keyBy('key');
        $permissions = $this->organization->permissions()->get()->keyBy('key');
        $now = now();

        $pivotRows = [];

        // loop through the mapping and prepare pivot rows for batch insertion
        foreach ($mapping as $roleKey => $permissionKeys) {
            $role = $roles->get($roleKey);

            // if the role doesn't exist, skip to the next one
            if (! $role) {
                continue;
            }

            // loop through the permission keys for this role and prepare pivot rows
            foreach ($permissionKeys as $permissionKey) {
                $permission = $permissions->get($permissionKey);

                // if the permission doesn't exist, skip to the next one
                if (! $permission) {
                    continue;
                }

                // prepare a pivot row for the role-permission relationship
                $pivotRows[] = [
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // insert the pivot rows into the permission_role table in a single query
        if ($pivotRows !== []) {
            DB::table('permission_role')->insert($pivotRows);
        }
    }

    private function assignFirstUserAsOwner(): void
    {
        $firstMember = $this->organization->members()->first();

        if ($firstMember) {
            $firstMember->update([
                'role_id' => $this->organization->roles()->where('key', 'owner')->first()->id,
            ]);
        }
    }
}
