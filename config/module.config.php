<?php
namespace FacetedBrowse;

use Laminas\Router\Http;

return [
    'faceted_browse_facet_types' => [
        'factories' => [
            'value' => Service\FacetType\FacetTypeFactory::class,
            'resource_class' => Service\FacetType\FacetTypeFactory::class,
            'resource_template' => Service\FacetType\FacetTypeFactory::class,
            'item_set' => Service\FacetType\FacetTypeFactory::class,
            'full_text' => Service\FacetType\FacetTypeFactory::class,
        ],
    ],
    'faceted_browse_column_types' => [
        'factories' => [
            'title' => Service\ColumnType\ColumnTypeFactory::class,
            'value' => Service\ColumnType\ColumnTypeFactory::class,
            'resource_class' => Service\ColumnType\ColumnTypeFactory::class,
            'resource_template' => Service\ColumnType\ColumnTypeFactory::class,
            'item_set' => Service\ColumnType\ColumnTypeFactory::class,
            'id' => Service\ColumnType\ColumnTypeFactory::class,
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
            'FacetedBrowse\ColumnTypeManager' => Service\ColumnType\ManagerFactory::class,
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
            'FacetedBrowse\Controller\SiteAdmin\Index' => Controller\SiteAdmin\IndexController::class,
            'FacetedBrowse\Controller\Site\Page' => Controller\Site\PageController::class,
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
    'navigation_links' => [
        'invokables' => [
            'facetedBrowse' => Site\NavigationLink\FacetedBrowse::class,
        ],
    ],
    'js_translate_strings' => [
        'A facet must have a name.', // @translate
        'A column must have a name.', // @translate
        'A column must have a property.', // @translate
        'Error fetching browse markup.', // @translate
        'Error fetching facet markup.', // @translate
        'Error fetching category markup.', // @translate
        'Cannot show all. The result set is likely too large.', // @translate
        'Loading results…', // @translate
    ],
    'navigation' => [
        'site' => [
            [
                'label' => 'Faceted Browse', // @translate
                'route' => 'admin/site/slug/faceted-browse',
                'controller' => 'index',
                'action' => 'index',
                'useRouteMatch' => true,
                'resource' => 'FacetedBrowse\Controller\SiteAdmin\Index',
                'pages' => [
                    [
                        'route' => 'admin/site/slug/faceted-browse-page',
                        'controller' => 'page',
                        'visible' => false,
                    ],
                    [
                        'route' => 'admin/site/slug/faceted-browse-page-id',
                        'controller' => 'page',
                        'visible' => false,
                    ],
                    [
                        'route' => 'admin/site/slug/faceted-browse-category',
                        'controller' => 'category',
                        'visible' => false,
                    ],
                    [
                        'route' => 'admin/site/slug/faceted-browse-category-id',
                        'controller' => 'category',
                        'visible' => false,
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'site' => [
                'child_routes' => [
                    'faceted-browse' => [
                        'type' => Http\Segment::class,
                        'options' => [
                            'route' => '/faceted-browse/:page-id[/:action]',
                            'constraints' => [
                                'page-id' => '\d+',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'FacetedBrowse\Controller\Site',
                                'controller' => 'page',
                                'action' => 'page',
                            ],
                        ],
                    ],
                ],
            ],
            'admin' => [
                'child_routes' => [
                    'site' => [
                        'child_routes' => [
                            'slug' => [
                                'child_routes' => [
                                    'faceted-browse' => [
                                        'type' => Http\Segment::class,
                                        'options' => [
                                            'route' => '/faceted-browse[/:action]',
                                            'constraints' => [
                                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                            ],
                                            'defaults' => [
                                                '__NAMESPACE__' => 'FacetedBrowse\Controller\SiteAdmin',
                                                'controller' => 'index',
                                                'action' => 'index',
                                            ],
                                        ],
                                    ],
                                    'faceted-browse-page' => [
                                        'type' => Http\Segment::class,
                                        'options' => [
                                            'route' => '/faceted-browse/page[/:action]',
                                            'constraints' => [
                                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                            ],
                                            'defaults' => [
                                                '__NAMESPACE__' => 'FacetedBrowse\Controller\SiteAdmin',
                                                'controller' => 'page',
                                                'action' => 'browse',
                                            ],
                                        ],
                                    ],
                                    'faceted-browse-page-id' => [
                                        'type' => Http\Segment::class,
                                        'options' => [
                                            'route' => '/faceted-browse/:page-id[/:action]',
                                            'constraints' => [
                                                'page-id' => '\d+',
                                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                            ],
                                            'defaults' => [
                                                '__NAMESPACE__' => 'FacetedBrowse\Controller\SiteAdmin',
                                                'controller' => 'page',
                                                'action' => 'edit',
                                            ],
                                        ],
                                    ],
                                    'faceted-browse-category' => [
                                        'type' => Http\Segment::class,
                                        'options' => [
                                            'route' => '/faceted-browse/:page-id/category[/:action]',
                                            'constraints' => [
                                                'page-id' => '\d+',
                                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                            ],
                                            'defaults' => [
                                                '__NAMESPACE__' => 'FacetedBrowse\Controller\SiteAdmin',
                                                'controller' => 'category',
                                                'action' => 'browse',
                                            ],
                                        ],
                                    ],
                                    'faceted-browse-category-id' => [
                                        'type' => Http\Segment::class,
                                        'options' => [
                                            'route' => '/faceted-browse/:page-id/:category-id[/:action]',
                                            'constraints' => [
                                                'page-id' => '\d+',
                                                'category-id' => '\d+',
                                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                            ],
                                            'defaults' => [
                                                '__NAMESPACE__' => 'FacetedBrowse\Controller\SiteAdmin',
                                                'controller' => 'category',
                                                'action' => 'edit',
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
