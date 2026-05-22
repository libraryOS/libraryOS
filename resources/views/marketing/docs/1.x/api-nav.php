<?php

declare(strict_types=1);

return [
    [
        'label' => 'Introduction',
        'route' => 'marketing.docs.api.index',
        'versioned' => true,
        'active_routes' => ['marketing.docs.api.index'],
    ],
    [
        'label' => 'Organizations',
        'active_routes' => ['marketing.docs.api.organizations.*'],
        'children' => [
            [
                'label' => 'Organizations',
                'route' => 'marketing.docs.api.organizations.index',
                'versioned' => true,
                'active_routes' => ['marketing.docs.api.organizations.index'],
            ],
            [
                'label' => 'Adminland',
                'active_routes' => [
                    'marketing.docs.api.organizations.officetypes.*',
                    'marketing.docs.api.organizations.offices.*',
                    'marketing.docs.api.organizations.members.*',
                    'marketing.docs.api.organizations.membertypes.*',
                    'marketing.docs.api.organizations.departments.*',
                ],
                'children' => [
                    [
                        'label' => 'Office Types',
                        'route' => 'marketing.docs.api.organizations.officetypes.index',
                        'versioned' => true,
                        'active_routes' => ['marketing.docs.api.organizations.officetypes.*'],
                    ],
                    [
                        'label' => 'Offices',
                        'route' => 'marketing.docs.api.organizations.offices.index',
                        'versioned' => true,
                        'active_routes' => ['marketing.docs.api.organizations.offices.*'],
                    ],
                    [
                        'label' => 'Members',
                        'route' => 'marketing.docs.api.organizations.members.index',
                        'versioned' => true,
                        'active_routes' => ['marketing.docs.api.organizations.members.*'],
                    ],
                    [
                        'label' => 'Member Types',
                        'route' => 'marketing.docs.api.organizations.membertypes.index',
                        'versioned' => true,
                        'active_routes' => ['marketing.docs.api.organizations.membertypes.*'],
                    ],
                    [
                        'label' => 'Departments',
                        'route' => 'marketing.docs.api.organizations.departments.index',
                        'versioned' => true,
                        'active_routes' => ['marketing.docs.api.organizations.departments.*'],
                    ],
                ],
            ],
        ],
    ],
];
