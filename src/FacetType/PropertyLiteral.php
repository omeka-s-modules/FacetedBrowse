<?php
namespace FacetedBrowse\FacetType;

use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Fieldset;
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
        $js = sprintf('facetedBrowsePropertyLiteralValuesUrl = "%s";', $view->escapeJs($view->url(null, ['action' => 'property-literal-values'], true)));
        $view->headScript()->appendScript($js);
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
        // Property ID
        $elementPropertyId = $this->formElements->get(OmekaElement\PropertySelect::class);
        $elementPropertyId->setName('property_id')
            ->setOptions([
                'label' => 'Property', // @translate
                'empty_option' => 'Select one…', // @translate
            ])
            ->setAttributes([
                'id' => 'property-literal-property-id',
                'value' => $data['property_id'] ?? null,
            ]);
        // Query type
        $elementQueryType = $this->formElements->get(LaminasElement\Select::class);
        $elementQueryType->setName('query_type')
            ->setOptions([
                'label' => 'Query type', // @translate
                'value_options' => [
                    'eq' => 'Is exactly', // @translate
                    'in' => 'Contains', // @translate
                ],
            ])
            ->setAttributes([
                'id' => 'property-literal-query-type',
                'value' => $data['query_type'] ?? 'eq',
            ]);
        // Values
        $elementValues = $this->formElements->get(LaminasElement\Textarea::class);
        $elementValues->setName('values')
            ->setOptions([
                'label' => 'Values', // @translate
            ])
            ->setAttributes([
                'id' => 'property-literal-values',
                'style' => 'height: 300px;',
                'value' => $data['values'] ?? null,
            ]);

        return $view->partial('common/faceted-browse/facet-data-form/property-literal', [
            'elementPropertyId' => $elementPropertyId,
            'elementQueryType' => $elementQueryType,
            'elementValues' => $elementValues,
        ]);
    }
}
