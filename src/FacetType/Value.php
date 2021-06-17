<?php
namespace FacetedBrowse\FacetType;

use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
use Laminas\Form\Element as LaminasElement;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Form\Element as OmekaElement;

class Value implements FacetTypeInterface
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

    public function getMaxFacets() : ?int
    {
        return null;
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-data-form/value.js', 'FacetedBrowse'));
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
            'id' => 'value-property-id',
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
                'neq' => 'Is not exactly', // @translate
                'in' => 'Contains', // @translate
                'nin' => 'Does not contain', // @translate
                'res' => 'Is resource with ID', // @translate
                'nres' => 'Is not resource with ID', // @translate
                'ex' => 'Has any value', // @translate
                'nex' => 'Has no values', // @translate
            ],
        ]);
        $queryType->setAttributes([
            'id' => 'value-query-type',
            'value' => $data['query_type'] ?? 'eq',
        ]);
        // Select type
        $selectType = $this->formElements->get(LaminasElement\Select::class);
        $selectType->setName('select_type');
        $selectType->setOptions([
            'label' => 'Select type', // @translate
            'info' => 'Select the select type. For the "single" select type, users may choose only one value at a time via a list or dropdown menu. For the "multiple" select type, users may choose any number of values at a time via a list.', // @translate
            'value_options' => [
                'single_list' => 'Single (list)',
                'multiple_list' => 'Multiple (list)',
                'single_select' => 'Single (dropdown menu)',
            ],
        ]);
        $selectType->setAttributes([
            'id' => 'value-select-type',
            'value' => $data['select_type'] ?? 'single_list',
        ]);
        // Values
        $values = $this->formElements->get(LaminasElement\Textarea::class);
        $values->setName('values');
        $values->setOptions([
            'label' => 'Values', // @translate
            'info' => $view->translate('
            <p>Enter the values, separated by new lines. The format of each value depends on the query type:</p>
            <ul>
                <li>"Is exactly": enter a value that is an exact match to the property value.</li>
                <li>"Contains": enter a value that matches any part of the property value.</li>
                <li>"Is resource with ID": enter the resource ID followed by any value (usually the resource title), separated by a single space.</li>
                <li>"Has any value": enter the property ID followed by any value (usually the property label), separated by a single space.</li>
            </ul>'),
            'escape_info' => false,
        ]);
        $values->setAttributes([
            'id' => 'value-values',
            'style' => 'height: 300px;',
            'value' => $data['values'] ?? null,
        ]);

        return $view->partial('common/faceted-browse/facet-data-form/value', [
            'elementPropertyId' => $propertyId,
            'elementQueryType' => $queryType,
            'elementSelectType' => $selectType,
            'elementValues' => $values,
        ]);
    }

    public function prepareFacet(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-render/value.js', 'FacetedBrowse'));
    }

    public function renderFacet(PhpRenderer $view, FacetedBrowseFacetRepresentation $facet) : string
    {
        $values = $facet->data('values');
        $values = explode("\n", $values);
        $values = array_map('trim', $values);
        $values = array_unique($values);
        switch ($facet->data('query_type')) {
            case 'res':
            case 'nres':
            case 'ex':
            case 'nex':
                $idKeyValues = [];
                foreach ($values as $value) {
                    if (preg_match('/^(\d+) (.+)/', $value, $matches)) {
                        $idKeyValues[$matches[1]] = $matches[2];
                    } elseif (preg_match('/^(\d+)/', $value, $matches)) {
                        $idKeyValues[$matches[1]] = null;
                    }
                }
                $values = $idKeyValues;
                break;
            case 'eq':
            case 'neq':
            case 'in':
            case 'nin':
            default:
                $values = array_combine($values, $values);
        }

        $singleSelect = null;
        if ('single_select' === $facet->data('select_type')) {
            // Prepare "Single select" select type.
            $valueOptions = [];
            foreach ($values as $key => $value) {
                $dataPropertyId = $facet->data('property_id');
                $dataValue = $key;
                if (in_array($facet->data('query_type'), ['ex', 'nex'])) {
                    $dataPropertyId = $key;
                }
                $valueOptions[] = [
                    'value' => $key,
                    'label' => $value,
                    'attributes' => [
                        'data-property-id' => $dataPropertyId,
                        'data-value' => $dataValue,
                    ],
                ];
            }
            $singleSelect = $this->formElements->get(LaminasElement\Select::class);
            $singleSelect->setName(sprintf('value_%s', $facet->id()));
            $singleSelect->setValueOptions($valueOptions);
            $singleSelect->setEmptyOption('Select one…');
            $singleSelect->setAttribute('class', 'value');
        }

        return $view->partial('common/faceted-browse/facet-render/value', [
            'facet' => $facet,
            'values' => $values,
            'singleSelect' => $singleSelect,
        ]);

    }
}
