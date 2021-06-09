<?php
namespace FacetedBrowse\ViewHelper;

use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
use FacetedBrowse\ColumnType\ColumnTypeInterface;
use FacetedBrowse\FacetType\FacetTypeInterface;
use FacetedBrowse\ColumnType\Unknown as UnknownColumnType;
use FacetedBrowse\FacetType\Unknown as UnknownFacetType;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\AbstractHelper;

class FacetedBrowse extends AbstractHelper
{
    protected $services;

    public function __construct(ServiceLocatorInterface $services)
    {
        $this->services = $services;
    }

    /**
     * Get a facet type by name.
     *
     * @param string $facetType
     * @return FacetedBrowse\FacetType\FacetTypeInterface
     */
    public function getFacetType($facetType)
    {
        return $this->services->get('FacetedBrowse\FacetTypeManager')->get($facetType);
    }

    /**
     * Get a columns type by name.
     *
     * @param string $columnType
     * @return FacetedBrowse\ColumnType\FacetTypeInterface
     */
    public function getColumnType($columnType)
    {
        return $this->services->get('FacetedBrowse\ColumnTypeManager')->get($columnType);
    }

    /**
     * Prepare the data forms for all facet types.
     */
    public function prepareDataForms()
    {
        $facetTypes = $this->services->get('FacetedBrowse\FacetTypeManager');
        foreach ($facetTypes->getRegisteredNames() as $facetTypeName) {
            $this->getFacetType($facetTypeName)->prepareDataForm($this->getView());
        }
        $columnTypes = $this->services->get('FacetedBrowse\ColumnTypeManager');
        foreach ($columnTypes->getRegisteredNames() as $columnTypeName) {
            $this->getColumnType($columnTypeName)->prepareDataForm($this->getView());
        }
    }

    /**
     * Is this facet type known?
     *
     * @param FacetTypeInterface $facetType
     * @return bool
     */
    public function facetTypeIsKnown(FacetTypeInterface $facetType)
    {
        return !($facetType instanceof UnknownFacetType);
    }

    /**
     * Is this column type known?
     *
     * @param ColumnTypeInterface $columnType
     * @return bool
     */
    public function columnTypeIsKnown(ColumnTypeInterface $columnType)
    {
        return !($columnType instanceof UnknownColumnType);
    }

    /**
     * Is this facet of a type that is known?
     *
     * @param FacetedBrowseFacetRepresentation $facet
     * @return bool
     */
    public function facetIsKnown(FacetedBrowseFacetRepresentation $facet)
    {
        $facetType = $this->getFacetType($facet->type());
        return $this->facetTypeIsKnown($facetType);
    }

    /**
     * Prepare the facets for all facet types.
     */
    public function prepareFacets()
    {
        $facetTypes = $this->services->get('FacetedBrowse\FacetTypeManager');
        foreach ($facetTypes->getRegisteredNames() as $facetTypeName) {
            $this->getFacetType($facetTypeName)->prepareFacet($this->getView());
        }
    }

    /**
     * Render a facet.
     *
     * @param FacetedBrowseFacetRepresentation $facet
     * @return string
     */
    public function renderFacet(FacetedBrowseFacetRepresentation $facet)
    {
        $facetType = $this->getFacetType($facet->type());
        return $facetType->renderFacet($this->getView(), $facet);
    }
}
