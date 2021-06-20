<?php
namespace FacetedBrowse\FacetType;

use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
use Laminas\View\Renderer\PhpRenderer;

interface FacetTypeInterface
{
    /**
     * Get the label of this facet type.
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Get the resource types that can use this facet type.
     *
     * @return array
     */
    public function getResourceTypes() : array;

    /**
     * Get the maximum amount of this facet type for one category.
     *
     * @return ?int
     */
    public function getMaxFacets() : ?int;

    /**
     * Prepare the data form of this facet type.
     *
     * @param PhpRenderer $view
     */
    public function prepareDataForm(PhpRenderer $view) : void;

    /**
     * Render the data form of this facet type.
     *
     * @param PhpRenderer $view
     * @param array $data
     * @return string
     */
    public function renderDataForm(PhpRenderer $view, array $data) : string;

    /**
     * Prepare the render of this facet type.
     *
     * @param PhpRenderer $view
     */
    public function prepareFacet(PhpRenderer $view) : void;

    /**
     * Render the markup for this facet type.
     *
     * @param PhpRenderer $view
     * @param FacetedBrowseFacetRepresentation $facet
     * @return string
     */
    public function renderFacet(PhpRenderer $view, FacetedBrowseFacetRepresentation $facet) : string;
}
