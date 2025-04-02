<?php
namespace FacetedBrowse\Api\Representation;

use FacetedBrowse\Entity\FacetedBrowseFacet;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Omeka\Api\Representation\AbstractRepresentation;

class FacetedBrowseFacetRepresentation extends AbstractRepresentation
{
    protected $resource;

    public function __construct(FacetedBrowseFacet $facet, ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
        $this->resource = $facet;
    }

    public function jsonSerialize(): array
    {
        return [
            'o:id' => $this->id(),
            'o-module-faceted_browse:category' => $this->category()->getReference(),
            'o:name' => $this->name(),
            'o-module-faceted_browse:type' => $this->type(),
            'o:data' => $this->data(),
            'o:position' => $this->position(),
        ];
    }

    public function id()
    {
        return $this->resource->getId();
    }

    public function category()
    {
        return $this->getAdapter('faceted_browse_categories')->getRepresentation($this->resource->getCategory());
    }

    public function name()
    {
        return $this->resource->getName();
    }

    public function type()
    {
        return $this->resource->getType();
    }

    public function data($key = null, $default = null)
    {
        $data = $this->resource->getData();
        if ($key) {
            $data = $data[$key] ?? $default;
        }
        return $data;
    }

    public function position()
    {
        return $this->resource->getPosition();
    }
}
