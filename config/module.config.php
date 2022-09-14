<?php
namespace InverseProperties;

use Laminas\Router\Http;

return [
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => sprintf('%s/../language', __DIR__),
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            sprintf('%s/../view', __DIR__),
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            sprintf('%s/../src/Entity', __DIR__),
        ],
        'proxy_paths' => [
            sprintf('%s/../data/doctrine-proxies', __DIR__),
        ],
    ],
    'service_manager' => [
        'factories' => [
            'InverseProperties\InverseProperties' => Service\Stdlib\InversePropertiesFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            'InverseProperties\Controller\Admin\Index' => Service\Controller\Admin\IndexControllerFactory::class,
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Inverse Properties', // @translate
                'route' => 'admin/inverse-properties',
                'controller' => 'index',
                'action' => 'index',
                'resource' => 'InverseProperties\Controller\Admin\Index',
                'useRouteMatch' => true,
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'inverse-properties' => [
                        'type' => Http\Segment::class,
                        'options' => [
                            'route' => '/inverse-properties/resource-template',
                            'defaults' => [
                                '__NAMESPACE__' => 'InverseProperties\Controller\Admin',
                                'controller' => 'index',
                                'action' => 'resource-templates',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'resource-template-id' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:resource-template-id',
                                    'constraints' => [
                                        'resource-template-id' => '\d+',
                                    ],
                                    'defaults' => [
                                        'action' => 'properties',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
