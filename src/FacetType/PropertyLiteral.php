<?php
namespace FacetedBrowse\FacetType;

use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
use Laminas\Form\Element as LaminasElement;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Form\Element as OmekaElement;

class PropertyLiteral implements FacetTypeInterface
{
    protected $formElements;

    public function __construct(ServiceLocatorInterface $formElements)
    {
        $this->formElements = $formElements;
    }

    public function getLabel() : string
    {
        return 'Property'; // @translate
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-data-form/property_literal.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
        // Property ID
        $propertyId = $this->formElements->get(OmekaElement\PropertySelect::class);
        $propertyId->setName('property_id');
        $propertyId->setOptions([
            'label' => 'Property', // @translate
            'empty_option' => '',
        ]);
        $propertyId->setAttributes([
            'id' => 'property-literal-property-id',
            'value' => $data['property_id'] ?? null,
            'data-placeholder' => 'Select one…', // @translate
        ]);
        // Query type
        $queryType = $this->formElements->get(LaminasElement\Select::class);
        $queryType->setName('query_type');
        $queryType->setOptions([
            'label' => 'Query type', // @translate
            'info' => 'Select the query type. For "is exactly" facets, the value must be an exact match to the property value. For "contains" facets, the value may match any part of the property value.', // @translate
            'value_options' => [
                'eq' => 'Is exactly', // @translate
                'in' => 'Contains', // @translate
            ],
        ]);
        $queryType->setAttributes([
            'id' => 'property-literal-query-type',
            'value' => $data['query_type'] ?? 'eq',
        ]);
        // Select type
        $selectType = $this->formElements->get(LaminasElement\Select::class);
        $selectType->setName('select_type');
        $selectType->setOptions([
            'label' => 'Select type',
            'info' => 'Select the select type. For "single" facets, users may choose only one value at a time. For "multiple" facets, users may choose any number of values at a time.', // @translate
            'value_options' => [
                'single' => 'Single',
                'multiple' => 'Multiple',
            ],
        ]);
        $selectType->setAttributes([
            'id' => 'property-literal-select-type',
            'value' => $data['select_type'] ?? 'single',
        ]);
        // Values
        $values = $this->formElements->get(LaminasElement\Textarea::class);
        $values->setName('values');
        $values->setOptions([
            'label' => 'Values', // @translate
            'info' => 'Enter the values, separated by new lines.', // @translate
        ]);
        $values->setAttributes([
            'id' => 'property-literal-values',
            'style' => 'height: 300px;',
            'value' => $data['values'] ?? null,
        ]);

        return $view->partial('common/faceted-browse/facet-data-form/property-literal', [
            'elementPropertyId' => $propertyId,
            'elementQueryType' => $queryType,
            'elementSelectType' => $selectType,
            'elementValues' => $values,
        ]);
    }

    public function prepareFacet(PhpRenderer $view) : void
    {
        $view->headLink()->appendStylesheet($view->assetUrl('css/facet-render/property_literal.css', 'FacetedBrowse'));
        $view->headScript()->appendFile($view->assetUrl('js/facet-render/property_literal.js', 'FacetedBrowse'));
    }

    public function renderFacet(PhpRenderer $view, FacetedBrowseFacetRepresentation $facet) : string
    {
        $values = $facet->data('values');
        $values = explode("\n", $values);
        $values = array_map('trim', $values);
        $values = array_unique($values);

        return $view->partial('common/faceted-browse/facet-render/property-literal', [
            'facet' => $facet,
            'values' => $values,
        ]);
    }
}
