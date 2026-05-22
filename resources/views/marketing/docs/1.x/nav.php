<?php

declare(strict_types=1);

return [
    [
        'label' => 'Introduction',
        'route' => 'marketing.docs.index',
        'active_routes' => ['marketing.docs.index'],
    ],
    [
        'label' => 'Manage your organization',
        'active_routes' => [
            'marketing.docs.organizations.*',
            'marketing.docs.offices.*',
            'marketing.docs.departments.*',
        ],
        'children' => [
            [
                'label' => 'Getting started',
                'route' => 'marketing.docs.organizations.index',
                'versioned' => true,
                'active_routes' => ['marketing.docs.organizations.index'],
            ],
            [
                'label' => 'Manage offices',
                'active_routes' => ['marketing.docs.offices.*'],
                'children' => [
                    [
                        'label' => 'Getting started',
                        'route' => 'marketing.docs.offices.index',
                        'versioned' => true,
                        'active_routes' => ['marketing.docs.offices.index'],
                    ],
                    [
                        'label' => 'Manage offices',
                        'route' => 'marketing.docs.offices.manage',
                        'versioned' => true,
                        'active_routes' => ['marketing.docs.offices.manage'],
                    ],
                ],
            ],
            [
                'label' => 'Manage departments',
                'active_routes' => ['marketing.docs.departments.*'],
                'children' => [
                    [
                        'label' => 'Getting started',
                        'route' => 'marketing.docs.departments.index',
                        'versioned' => true,
                        'active_routes' => ['marketing.docs.departments.index'],
                    ],
                    [
                        'label' => 'Manage departments',
                        'route' => 'marketing.docs.departments.manage',
                        'versioned' => true,
                        'active_routes' => ['marketing.docs.departments.manage'],
                    ],
                ],
            ],
        ],
    ],
];
