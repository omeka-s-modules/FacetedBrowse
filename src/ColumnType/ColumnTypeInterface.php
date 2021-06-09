<?php
namespace FacetedBrowse\ColumnType;

use Laminas\View\Renderer\PhpRenderer;

interface ColumnTypeInterface
{
    /**
     * Get the label of this column type.
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Prepare the data form of this column type.
     *
     * @param PhpRenderer $view
     */
    public function prepareDataForm(PhpRenderer $view) : void;

    /**
     * Render the data form of this coilumn type.
     *
     * @param PhpRenderer $view
     * @param array $data
     * @return string
     */
    public function renderDataForm(PhpRenderer $view, array $data) : string;
}
