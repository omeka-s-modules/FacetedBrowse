<?php
namespace FacetedBrowse\ColumnType;

use FacetedBrowse\Api\Representation\FacetedBrowseColumnRepresentation;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\ItemRepresentation;

class ResourceTemplate implements ColumnTypeInterface
{
    protected $formElements;

    public function __construct(ServiceLocatorInterface $formElements)
    {
        $this->formElements = $formElements;
    }

    public function getLabel() : string
    {
        return 'Template'; // @translate
    }

    public function getMaxColumns() : ?int
    {
        return 1;
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/column-data-form/resource-template.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
        return '';
    }

    public function getSortBy(FacetedBrowseColumnRepresentation $column) : ?string
    {
        // Omeka does not provide a way to sort by resource template label.
        return null;
    }

    public function renderContent(FacetedBrowseColumnRepresentation $column, ItemRepresentation $item) : string
    {
        $template = $item->resourceTemplate();
        return $template ? $template->label() : '';
    }
}
