<?php
namespace FacetedBrowse\ColumnType;

use FacetedBrowse\Api\Representation\FacetedBrowseColumnRepresentation;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Form\Element as omekaElement;

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

        return $view->partial('common/faceted-browse/column-data-form/value', [
            'propertySelect' => $propertySelect,
        ]);
    }

    public function getSortBy(FacetedBrowseColumnRepresentation $column) : ?string
    {
        return $column->data('property_term');
    }

    public function renderContent(FacetedBrowseColumnRepresentation $column, ItemRepresentation $item) : string
    {
        $propertyTerm = $column->data('property_term');
        return implode('<br>', $item->value($propertyTerm, ['type' => 'literal', 'all' => true]));
    }
}
