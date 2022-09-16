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
        'invokables' => [
            'InverseProperties\Controller\Admin\Index' => Controller\Admin\IndexController::class,
        ],
        'factories' => [
            'InverseProperties\Controller\Admin\ResourceTemplate' => Service\Controller\Admin\ResourceTemplateControllerFactory::class,
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
                'pages' => [
                    [
                        'route' => 'admin/inverse-properties-resource-template',
                        'visible' => false,
                    ],
                    [
                        'route' => 'admin/inverse-properties-resource-template/id',
                        'visible' => false,
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'inverse-properties' => [
                        'type' => Http\Literal::class,
                        'options' => [
                            'route' => '/inverse-properties',
                            'defaults' => [
                                '__NAMESPACE__' => 'InverseProperties\Controller\Admin',
                                'controller' => 'index',
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'inverse-properties-resource-template' => [
                        'type' => Http\Segment::class,
                        'options' => [
                            'route' => '/inverse-properties/resource-template/:action',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'InverseProperties\Controller\Admin',
                                'controller' => 'resource-template',
                                'action' => 'browse',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'id' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:id',
                                    'constraints' => [
                                        'id' => '\d+',
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
