<?php
namespace FacetedBrowse\ColumnType;

use FacetedBrowse\Api\Representation\FacetedBrowseColumnRepresentation;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\ItemRepresentation;

class Title implements ColumnTypeInterface
{
    protected $formElements;

    public function __construct(ServiceLocatorInterface $formElements)
    {
        $this->formElements = $formElements;
    }

    public function getLabel() : string
    {
        return 'Title (link to resource)'; // @translate
    }

    public function getResourceTypes() : array
    {
        return ['items', 'item_sets', 'media'];
    }

    public function getMaxColumns() : ?int
    {
        return 1;
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/column-data-form/title.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
        return '';
    }

    public function getSortBy(FacetedBrowseColumnRepresentation $column) : ?string
    {
        return 'title';
    }

    public function renderContent(FacetedBrowseColumnRepresentation $column, ItemRepresentation $item) : string
    {
        return $item->linkPretty('square');
    }
}
