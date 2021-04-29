<?php
namespace FacetedBrowse\FacetType;

use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
use Laminas\Form\Element as LaminasElement;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Form\Element as OmekaElement;

class ValueLiteral implements FacetTypeInterface
{
    protected $formElements;

    public function __construct(ServiceLocatorInterface $formElements)
    {
        $this->formElements = $formElements;
    }

    public function getLabel() : string
    {
        return 'Value'; // @translate
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-data-form/value_literal.js', 'FacetedBrowse'));
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
            'id' => 'value-literal-property-id',
            'value' => $data['property_id'] ?? null,
            'data-placeholder' => '[Any property]', // @translate
        ]);
        // Query type
        $queryType = $this->formElements->get(LaminasElement\Select::class);
        $queryType->setName('query_type');
        $queryType->setOptions([
            'label' => 'Query type', // @translate
            'value_options' => [
                'eq' => 'Is exactly', // @translate
                'in' => 'Contains', // @translate
                'res' => 'Is resource with ID', // @translate
            ],
        ]);
        $queryType->setAttributes([
            'id' => 'value-literal-query-type',
            'value' => $data['query_type'] ?? 'eq',
        ]);
        // Select type
        $selectType = $this->formElements->get(LaminasElement\Select::class);
        $selectType->setName('select_type');
        $selectType->setOptions([
            'label' => 'Select type',
            'info' => 'Select the select type. For the "single" select type, users may choose only one value at a time. For the "multiple" select type, users may choose any number of values at a time.', // @translate
            'value_options' => [
                'single' => 'Single',
                'multiple' => 'Multiple',
            ],
        ]);
        $selectType->setAttributes([
            'id' => 'value-literal-select-type',
            'value' => $data['select_type'] ?? 'single',
        ]);
        // Values
        $values = $this->formElements->get(LaminasElement\Textarea::class);
        $values->setName('values');
        $values->setOptions([
            'label' => 'Values', // @translate
            'info' => 'Enter one value per line. For the "Is exactly" query type, enter a value that is an exact match to the property value. For the "Contains" query type, enter a value that matches any part of the property value. For the "Is resource with ID" query type, enter the resource ID followed by any value (usually the resource title) separated by a single space.', // @translate
        ]);
        $values->setAttributes([
            'id' => 'value-literal-values',
            'style' => 'height: 300px;',
            'value' => $data['values'] ?? null,
        ]);

        return $view->partial('common/faceted-browse/facet-data-form/value-literal', [
            'elementPropertyId' => $propertyId,
            'elementQueryType' => $queryType,
            'elementSelectType' => $selectType,
            'elementValues' => $values,
        ]);
    }

    public function prepareFacet(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-render/value_literal.js', 'FacetedBrowse'));
    }

    public function renderFacet(PhpRenderer $view, FacetedBrowseFacetRepresentation $facet) : string
    {
        $values = $facet->data('values');
        $values = explode("\n", $values);
        $values = array_map('trim', $values);
        $values = array_unique($values);
        if ('res' == $facet->data('query_type')) {
            $resValues = [];
            foreach ($values as $value) {
                if (preg_match('/^(\d+) (.+)/', $value, $matches)) {
                    $resValues[$matches[1]] = $matches[2];
                }
            }
            $values = $resValues;
        } else {
            $values = array_combine($values, $values);
        }

        return $view->partial('common/faceted-browse/facet-render/value-literal', [
            'facet' => $facet,
            'values' => $values,
        ]);
    }
}
