<?php
namespace FacetedBrowse\ViewHelper;

use FacetedBrowse\FacetType\FacetTypeInterface;
use FacetedBrowse\FacetType\Unknown;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\AbstractHelper;

class FacetedBrowse extends AbstractHelper
{
    protected $services;

    public function __construct(ServiceLocatorInterface $services)
    {
        $this->services = $services;
    }

    public function getFacetType($facetType)
    {
        return $this->services->get('FacetedBrowse\FacetTypeManager')->get($facetType);
    }

    public function prepareDataForms()
    {
        $facetTypes = $this->services->get('FacetedBrowse\FacetTypeManager');
        foreach ($facetTypes->getRegisteredNames() as $facetTypeName) {
            $this->getFacetType($facetTypeName)->prepareDataForm($this->getView());
        }
    }

    public function facetTypeIsKnown(FacetTypeInterface $facetType)
    {
        return !($facetType instanceof Unknown);
    }

    public function prepareFacets()
    {
        $facetTypes = $this->services->get('FacetedBrowse\FacetTypeManager');
        foreach ($facetTypes->getRegisteredNames() as $facetTypeName) {
            $this->getFacetType($facetTypeName)->prepareFacet($this->getView());
        }
    }

    public function renderFacet($facet)
    {
        $facetType = $this->getFacetType($facet->type());
        return $facetType->renderFacet($this->getView(), $facet);
    }
}
