<?php
namespace FacetedBrowse;

use Laminas\Router\Http;

return [
    'faceted_browse_facet_types' => [
        'factories' => [
            'property_literal' => Service\FacetType\PropertyLiteralFactory::class,
            //~ 'resource_class' => Service\FacetType\ResourceClass::class,
            //~ 'resource_template' => Service\FacetType\ResourceTemplate::class,
            //~ 'item_set' => Service\FacetType\ItemSet::class,
        ],
    ],
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
        'strategies' => [
            'ViewJsonStrategy',
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
            'FacetedBrowse\FacetTypeManager' => Service\FacetType\ManagerFactory::class,
        ],
    ],
    'api_adapters' => [
        'invokables' => [
            'faceted_browse_categories' => Api\Adapter\FacetedBrowseCategoryAdapter::class,
            'faceted_browse_pages' => Api\Adapter\FacetedBrowsePageAdapter::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'FacetedBrowse\Controller\SiteAdmin\Category' => Controller\SiteAdmin\CategoryController::class,
            'FacetedBrowse\Controller\SiteAdmin\Page' => Controller\SiteAdmin\PageController::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'facetedBrowse' => Service\ControllerPlugin\FacetedBrowseFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'facetedBrowse' => Service\ViewHelper\FacetedBrowseFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            'FacetedBrowse\Form\PageForm' => Service\Form\PageFormFactory::class,
            'FacetedBrowse\Form\CategoryForm' => Service\Form\CategoryFormFactory::class,
        ],
    ],
    'js_translate_strings' => [
        'A facet must have a name.', // @translate
        'A facet must have a query type.', // @translate
        'The selected property has no values.', // @translate
    ],
    'navigation' => [
        'site' => [
            [
                'label' => 'Faceted Browse', // @translate
                'route' => 'admin/site/slug/faceted-browse',
                'controller' => 'category',
                'action' => 'browse',
                'useRouteMatch' => true,
                'pages' => [
                    [
                        'label' => 'Categories', // @translate
                        'route' => 'admin/site/slug/faceted-browse',
                        'controller' => 'category',
                        'action' => 'browse',
                        'useRouteMatch' => true,
                        'pages' => [
                            [
                                'route' => 'admin/site/slug/faceted-browse',
                                'controller' => 'category',
                                'visible' => false,
                            ],
                            [
                                'route' => 'admin/site/slug/faceted-browse/id',
                                'controller' => 'category',
                                'visible' => false,
                            ],
                        ],
                    ],
                    [
                        'label' => 'Pages', // @translate
                        'route' => 'admin/site/slug/faceted-browse',
                        'controller' => 'page',
                        'action' => 'browse',
                        'useRouteMatch' => true,
                        'pages' => [
                            [
                                'route' => 'admin/site/slug/faceted-browse',
                                'controller' => 'page',
                                'visible' => false,
                            ],
                            [
                                'route' => 'admin/site/slug/faceted-browse/id',
                                'controller' => 'page',
                                'visible' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'site' => [
                        'child_routes' => [
                            'slug' => [
                                'child_routes' => [
                                    'faceted-browse' => [
                                        'type' => Http\Segment::class,
                                        'options' => [
                                            'route' => '/faceted-browse/:controller/:action',
                                            'constraints' => [
                                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                'id' => '\d+',
                                            ],
                                            'defaults' => [
                                                '__NAMESPACE__' => 'FacetedBrowse\Controller\SiteAdmin',
                                                'controller' => 'category',
                                                'action' => 'browse',
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            'id' => [
                                                'type' => Http\Segment::class,
                                                'options' => [
                                                    'route' => '/:id',
                                                ],
                                            ],
                                        ],
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
