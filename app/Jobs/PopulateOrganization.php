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
        $this->addDefaultItemTypes();
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
                'description' => 'Allows the user to access the adminland section of the organization.',
            ],
            [
                'key' => 'organization.update',
                'name_translation_key' => trans_key('Update organization'),
                'description' => 'Allows the user to update the organization information, such as its name, branding, and general configuration.',
            ],
            [
                'key' => 'organization.delete',
                'name_translation_key' => trans_key('Delete organization'),
                'description' => 'Allows the user to permanently delete the organization and all associated data.',
            ],
            [
                'key' => PermissionEnum::RoleManage->value,
                'name_translation_key' => trans_key('Manage roles'),
                'description' => 'Allows the user to manage role settings.',
            ],
            [
                'key' => PermissionEnum::BranchManage->value,
                'name_translation_key' => trans_key('Manage branches'),
                'description' => 'Allows the user to manage branches settings and configurations.',
            ],
            [
                'key' => PermissionEnum::ItemTypeManage->value,
                'name_translation_key' => trans_key('Manage item types'),
                'description' => 'Allows the user to manage item types for the organization.',
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
                PermissionEnum::ItemTypeManage->value,
            ],
            'administrator' => [
                PermissionEnum::AdminlandAccess->value,
                PermissionEnum::OrganizationUpdate->value,
                PermissionEnum::RoleManage->value,
                PermissionEnum::BranchManage->value,
                PermissionEnum::ItemTypeManage->value,
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

    private function addDefaultItemTypes(): void
    {
        $defaultItemTypes = [
            [
                'key' => 'book',
                'name_translation_key' => trans_key('Book'),
                'description' => 'General physical books such as novels, non-fiction, and textbooks.',
                'is_loanable' => true,
                'is_holdable' => true,
                'is_visible_in_catalog' => true,
                'default_loan_days' => 21,
            ],
            [
                'key' => 'manga',
                'name_translation_key' => trans_key('Manga'),
                'description' => 'Comic books and manga volumes intended for circulation.',
                'is_loanable' => true,
                'is_holdable' => true,
                'is_visible_in_catalog' => true,
                'default_loan_days' => 14,
            ],
            [
                'key' => 'magazine',
                'name_translation_key' => trans_key('Magazine'),
                'description' => 'Magazines and periodicals with shorter circulation periods.',
                'is_loanable' => true,
                'is_holdable' => true,
                'is_visible_in_catalog' => true,
                'default_loan_days' => 7,
            ],
            [
                'key' => 'reference',
                'name_translation_key' => trans_key('Reference'),
                'description' => 'Reference materials intended for on-site consultation only.',
                'is_loanable' => false,
                'is_holdable' => false,
                'is_visible_in_catalog' => true,
                'default_loan_days' => null,
            ],
            [
                'key' => 'dvd',
                'name_translation_key' => trans_key('DVD'),
                'description' => 'Video and audiovisual media distributed on DVD.',
                'is_loanable' => true,
                'is_holdable' => true,
                'is_visible_in_catalog' => true,
                'default_loan_days' => 7,
            ],
            [
                'key' => 'board_game',
                'name_translation_key' => trans_key('Board game'),
                'description' => 'Board games and tabletop games available for borrowing.',
                'is_loanable' => true,
                'is_holdable' => true,
                'is_visible_in_catalog' => true,
                'default_loan_days' => 14,
            ],
            [
                'key' => 'ebook',
                'name_translation_key' => trans_key('E-book'),
                'description' => 'Digital books accessible electronically through the catalog.',
                'is_loanable' => false,
                'is_holdable' => false,
                'is_visible_in_catalog' => true,
                'default_loan_days' => null,
            ],
        ];

        $this->organization->itemTypes()->createMany($defaultItemTypes);
    }
}
