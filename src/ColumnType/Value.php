<?php
namespace FacetedBrowse\ColumnType;

use FacetedBrowse\Api\Representation\FacetedBrowseColumnRepresentation;
use Laminas\Form\Element as LaminasElement;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Form\Element as OmekaElement;

class Value implements ColumnTypeInterface
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

    public function getMaxColumns() : ?int
    {
        return null;
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/column-data-form/value.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
        $propertySelect = $this->formElements->get(OmekaElement\PropertySelect::class);
        $propertySelect->setName('property_term');
        $propertySelect->setOptions([
            'label' => 'Property', // @translate
            'empty_option' => '',
            'term_as_value' => true,
        ]);
        $propertySelect->setAttributes([
            'id' => 'value-property-terms',
            'value' => $data['property_term'] ?? null,
            'data-placeholder' => 'Select a property…', // @translate
            'required' => true,
        ]);

        $maxValuesInput = $this->formElements->get(LaminasElement\Number::class);
        $maxValuesInput->setName('max_values');
        $maxValuesInput->setOptions([
            'label' => 'Max values', // @translate
            'info' => 'Enter the maximum number of values to display. Set to blank to display all values.', // @translate
        ]);
        $maxValuesInput->setAttributes([
            'id' => 'value-max-values',
            'value' => $data['max_values'] ?? 1,
            'min' => 1,
            'step' => 1,
        ]);

        return $view->partial('common/faceted-browse/column-data-form/value', [
            'propertySelect' => $propertySelect,
            'maxValuesInput' => $maxValuesInput,
        ]);
    }

    public function getSortBy(FacetedBrowseColumnRepresentation $column) : ?string
    {
        return $column->data('property_term');
    }

    public function renderContent(FacetedBrowseColumnRepresentation $column, ItemRepresentation $item) : string
    {
        $propertyTerm = $column->data('property_term');
        $maxValues = $column->data('max_values');

        // Get the values.
        $values = $item->value($propertyTerm, ['all' => true]);
        if ($maxValues) {
            $values = array_slice($values, 0, $maxValues);
        }

        // Prepare the content.
        $content = [];
        foreach ($values as $value) {
            $content[] = $value->asHtml();
        }

        return implode('<br>', $content);
    }
}
