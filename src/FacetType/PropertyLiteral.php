<?php
namespace FacetedBrowse\FacetType;

use Omeka\Form\Element as OmekaElement;
use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Fieldset;
use Laminas\View\Renderer\PhpRenderer;

class PropertyLiteral implements FacetTypeInterface
{
    public function getLabel() : string
    {
        return 'Property'; // @translate
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-data-form/property_literal.js', 'FacetedBrowse'));
        $js = sprintf('facetedBrowsePropertyLiteralValuesUrl = "%s";', $view->escapeJs($view->url(null, ['action' => 'property-literal-values'], true)));
        $view->headScript()->appendScript($js);
    }

    public function setDataElements(Fieldset $fieldset, array $data) : void
    {
        $fieldset->add([
            'type' => OmekaElement\PropertySelect::class,
            'name' => 'property_id',
            'options' => [
                'label' => 'Property', // @translate
                'empty_option' => 'Select one…', // @translate
            ],
            'attributes' => [
                'id' => 'property-literal-property-id',
                'value' => $data['property_id'] ?? null,
            ],
        ]);
        $fieldset->add([
            'type' => LaminasElement\Select::class,
            'name' => 'query_type',
            'options' => [
                'label' => 'Query type',
                'value_options' => [
                    'eq' => 'Is exactly', // @translate
                    'in' => 'Contains', // @translate
                ],
            ],
            'attributes' => [
                'id' => 'property-literal-query-type',
                'value' => $data['query_type'] ?? 'eq',
            ],
        ]);
        $fieldset->add([
            'type' => LaminasElement\Textarea::class,
            'name' => 'values',
            'options' => [
                'label' => 'Values', // @translate
            ],
            'attributes' => [
                'id' => 'property-literal-values',
                'style' => 'height: 300px;',
                'value' => $data['values'] ?? null,
            ],
        ]);
    }
}
