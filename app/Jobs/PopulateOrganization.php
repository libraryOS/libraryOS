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
        $this->addDefaultPatronTypes();
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
            [
                'key' => PermissionEnum::PatronTypeManage->value,
                'name_translation_key' => trans_key('Manage patron types'),
                'description' => 'Allows the user to manage patron types for the organization.',
            ],
            [
                'key' => PermissionEnum::LocationManage->value,
                'name_translation_key' => trans_key('Manage locations'),
                'description' => 'Allows the user to manage locations for the organization.',
            ],
            [
                'key' => PermissionEnum::PatronView->value,
                'name_translation_key' => trans_key('View patrons'),
                'description' => 'Voir la liste et la fiche d’un patron.',
            ],
            [
                'key' => PermissionEnum::PatronCreate->value,
                'name_translation_key' => trans_key('Create patrons'),
                'description' => 'Créer un nouveau patron.',
            ],
            [
                'key' => PermissionEnum::PatronUpdate->value,
                'name_translation_key' => trans_key('Update patrons'),
                'description' => 'Modifier les informations du patron.',
            ],
            [
                'key' => PermissionEnum::PatronArchive->value,
                'name_translation_key' => trans_key('Archive patrons'),
                'description' => 'Désactiver/archiver un patron sans le supprimer.',
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
                PermissionEnum::PatronTypeManage->value,
                PermissionEnum::LocationManage->value,
                PermissionEnum::PatronView->value,
                PermissionEnum::PatronCreate->value,
                PermissionEnum::PatronUpdate->value,
                PermissionEnum::PatronArchive->value,
            ],
            'administrator' => [
                PermissionEnum::AdminlandAccess->value,
                PermissionEnum::OrganizationUpdate->value,
                PermissionEnum::RoleManage->value,
                PermissionEnum::BranchManage->value,
                PermissionEnum::ItemTypeManage->value,
                PermissionEnum::PatronTypeManage->value,
                PermissionEnum::LocationManage->value,
                PermissionEnum::PatronView->value,
                PermissionEnum::PatronCreate->value,
                PermissionEnum::PatronUpdate->value,
                PermissionEnum::PatronArchive->value,
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

    private function addDefaultPatronTypes(): void
    {
        $defaultPatronTypes = [
            [
                'key' => 'adult',
                'name' => 'Adult',
                'name_translation_key' => trans_key('Adult'),
                'description' => 'Standard adult patron account.',
                'is_active' => true,
                'membership_duration_days' => 365,
                'max_loans' => 20,
                'keep_loan_history' => false,
                'can_receive_notifications' => true,
                'minimum_age' => 18,
                'maximum_age' => null,
            ],
            [
                'key' => 'child',
                'name' => 'Child',
                'name_translation_key' => trans_key('Child'),
                'description' => 'Patron account intended for children.',
                'is_active' => true,
                'membership_duration_days' => 365,
                'max_loans' => 10,
                'keep_loan_history' => false,
                'can_receive_notifications' => true,
                'minimum_age' => 0,
                'maximum_age' => 12,
            ],
            [
                'key' => 'student',
                'name' => 'Student',
                'name_translation_key' => trans_key('Student'),
                'description' => 'Patron account intended for students.',
                'is_active' => true,
                'membership_duration_days' => 365,
                'max_loans' => 15,
                'keep_loan_history' => false,
                'can_receive_notifications' => true,
                'minimum_age' => 13,
                'maximum_age' => null,
            ],
            [
                'key' => 'teacher',
                'name' => 'Teacher',
                'name_translation_key' => trans_key('Teacher'),
                'description' => 'Patron account intended for teachers and educators.',
                'is_active' => true,
                'membership_duration_days' => 365,
                'max_loans' => 50,
                'keep_loan_history' => false,
                'can_receive_notifications' => true,
                'minimum_age' => 18,
                'maximum_age' => null,
            ],
            [
                'key' => 'staff',
                'name' => 'Staff',
                'name_translation_key' => trans_key('Staff'),
                'description' => 'Patron account intended for library staff members.',
                'is_active' => true,
                'membership_duration_days' => 365,
                'max_loans' => 30,
                'keep_loan_history' => false,
                'can_receive_notifications' => true,
                'minimum_age' => 18,
                'maximum_age' => null,
            ],
            [
                'key' => 'temporary',
                'name' => 'Temporary',
                'name_translation_key' => trans_key('Temporary'),
                'description' => 'Short-term patron account with limited borrowing privileges.',
                'is_active' => true,
                'membership_duration_days' => 30,
                'max_loans' => 5,
                'keep_loan_history' => false,
                'can_receive_notifications' => true,
                'minimum_age' => null,
                'maximum_age' => null,
            ],
        ];

        $this->organization->patronTypes()->createMany($defaultPatronTypes);
    }
}
