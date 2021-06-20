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
     * Get the resource types that can use this column type.
     *
     * @return array
     */
    public function getResourceTypes() : array;

    /**
     * Get the maximum amount of this column type for one category.
     *
     * @return ?int
     */
    public function getMaxColumns() : ?int;

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
     * Get the corresponding sort_by value of this column type.
     *
     * @param FacetedBrowseColumnRepresentation $column
     * @return ?string
     */
    public function getSortBy(FacetedBrowseColumnRepresentation $column) : ?string;

    /**
     * Render the content of a column of this type.
     *
     * @param FacetedBrowseColumnRepresentation $column
     * @param ItemRepresentation $item
     * @return string
     */
    public function renderContent(FacetedBrowseColumnRepresentation $column, ItemRepresentation $item) : string;
}
