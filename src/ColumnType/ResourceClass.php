<?php
namespace FacetedBrowse\ColumnType;

use FacetedBrowse\Api\Representation\FacetedBrowseColumnRepresentation;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\ItemRepresentation;

class ResourceClass implements ColumnTypeInterface
{
    protected $formElements;

    public function __construct(ServiceLocatorInterface $formElements)
    {
        $this->formElements = $formElements;
    }

    public function getLabel() : string
    {
        return 'Class'; // @translate
    }

    public function getMaxColumns() : ?int
    {
        return 1;
    }

    public function getSortBy() : ?string
    {
        return 'resource_class_label';
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/column-data-form/resource-class.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
        return $view->partial('common/faceted-browse/column-data-form/resource-class', []);
    }

    public function renderContent(ItemRepresentation $item, FacetedBrowseColumnRepresentation $column) : string
    {
        return $item->resourceClass()->label();
    }
}
