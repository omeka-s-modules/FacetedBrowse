<?php
namespace FacetedBrowse\Form;

use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Form;
use Omeka\Form\Element as OmekaElement;

class CategoryForm extends Form
{
    public function init()
    {
        $site = $this->getOption('site');
        $facetTypes = $this->getOption('facet_types');
        $columnTypes = $this->getOption('column_types');
        $sortByValueOptions = $this->getOption('sort_by_value_options');
        $category = $this->getOption('category');
        $page = $this->getOption('page');

        $this->add([
            'type' => LaminasElement\Text::class,
            'name' => 'o:name',
            'options' => [
                'label' => 'Name', // @translate
                'info' => 'Enter the name of this category. A category is a logical grouping of resources that can be described using faceted classification.', // @translate
            ],
            'attributes' => [
                'id' => 'category-name',
                'required' => true,
            ],
        ]);
        $this->add([
            'type' => OmekaElement\Query::class,
            'name' => 'o:query',
            'options' => [
                'label' => 'Search query', // @translate
                'info' => 'Configure the logical grouping of resources that make up this category. No query means all site resources.', // @translate
                'query_resource_type' => $page->resourceType(),
                'query_partial_excludelist' => [
                    'common/advanced-search/site',
                    'common/advanced-search/sort',
                ],
                'query_preview_append_query' => ['site_id' => $site->id()],
            ],
            'attributes' => [
                'id' => 'category-query',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Select::class,
            'name' => 'o-module-faceted_browse:sort_by',
            'options' => [
                'label' => 'Default sort by',
                'value_options' => $sortByValueOptions,
            ],
            'attributes' => [
                'id' => 'category-sort-by',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Select::class,
            'name' => 'o-module-faceted_browse:sort_order',
            'options' => [
                'label' => 'Default sort order',
                'value_options' => [
                    'asc' => 'Ascending',
                    'desc' => 'Descending',
                ],
            ],
            'attributes' => [
                'id' => 'category-sort-order',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Select::class,
            'name' => 'o-module-faceted_browse:sort_by',
            'options' => [
                'label' => 'Default sort by',
                'value_options' => $sortByValueOptions,
            ],
            'attributes' => [
                'id' => 'category-sort-by',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Select::class,
            'name' => 'facet_type',
            'options' => [
                'label' => 'Facet type', // @translate
                'empty_option' => 'Add a facet…', // @translate
                'value_options' => $facetTypes->getValueOptions($page),
            ],
            'attributes' => [
                'id' => 'facet-type-select',
                'aria-labelledby' => 'facet-add-button',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Select::class,
            'name' => 'column_type',
            'options' => [
                'label' => 'Column type', // @translate
                'empty_option' => 'Add a column…', // @translate
                'value_options' => $columnTypes->getValueOptions($page),
            ],
            'attributes' => [
                'id' => 'column-type-select',
                'aria-labelledby' => 'column-add-button',
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'o:query',
            'allow_empty' => true,
        ]);
        $inputFilter->add([
            'name' => 'facet_type',
            'allow_empty' => true,
        ]);
        $inputFilter->add([
            'name' => 'column_type',
            'allow_empty' => true,
        ]);
    }
}
