<?php
namespace FacetedBrowse\ColumnType;

use FacetedBrowse\Api\Representation\FacetedBrowseColumnRepresentation;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\ItemRepresentation;

interface ColumnTypeInterface
{
    /**
     * Get the label of this column type.
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Get the maximum amount of this column type for one category.
     *
     * @return ?int
     */
    public function getMaxColumns() : ?int;

    /**
     * Get the corresponding sort_by value of this column type.
     *
     * @return ?string
     */
    public function getSortBy() : ?string;

    /**
     * Prepare the data form of this column type.
     *
     * @param PhpRenderer $view
     */
    public function prepareDataForm(PhpRenderer $view) : void;

    /**
     * Render the data form of this column type.
     *
     * @param PhpRenderer $view
     * @param array $data
     * @return string
     */
    public function renderDataForm(PhpRenderer $view, array $data) : string;

    /**
     * Render the content of a column of this type.
     *
     * @param ItemRepresentation $item
     * @param FacetedBrowseColumnRepresentation $column
     * @return string
     */
    public function renderContent(ItemRepresentation $item, FacetedBrowseColumnRepresentation $column) : string;
}
