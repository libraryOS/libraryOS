<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Organization;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
                'key' => 'organization.view',
                'name' => trans_key('View organization'),
                'description' => trans_key('Allows the user to view the organization settings and information.'),
                'is_system' => true,
            ],

            [
                'key' => 'organization.update',
                'name' => trans_key('Update organization'),
                'description' => trans_key('Allows the user to update the organization settings and information.'),
                'is_system' => true,
            ],
        ];

        $this->organization->permissions()->createMany($permissionsData);
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
