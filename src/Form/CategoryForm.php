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
        $category = $this->getOption('category');

        $this->add([
            'type' => LaminasElement\Text::class,
            'name' => 'o:name',
            'options' => [
                'label' => 'Name', // @translate
                'info' => 'Enter the name of this category. A category is a logical grouping of items that can be described using faceted classification.', // @translate
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
                'info' => 'Configure the logical grouping of items that make up this category. No query means all site resources.', // @translate
                'query_resource_type' => 'items',
                'query_partial_excludelist' => ['common/advanced-search/site'],
                'query_preview_append_query' => ['site_id' => $site->id()],
            ],
            'attributes' => [
                'id' => 'category-query',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Select::class,
            'name' => 'facet_type',
            'options' => [
                'label' => 'Facet type', // @translate
                'empty_option' => 'Add a facet', // @translate
                'value_options' => $facetTypes->getValueOptions(),
            ],
            'attributes' => [
                'id' => 'facet-type-select',
                'aria-labelledby' => 'facet-add-button',
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
    }
}
