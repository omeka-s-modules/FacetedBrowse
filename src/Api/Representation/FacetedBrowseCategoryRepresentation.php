<?php
namespace FacetedBrowse\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class FacetedBrowseCategoryRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLdType()
    {
        return 'o-module-faceted_browse:Category';
    }

    public function getJsonLd()
    {
        $owner = $this->owner();
        $modified = $this->modified();
        return [
            'o:site' => $this->site()->getReference(),
            'o:owner' => $owner ? $owner->getReference() : null,
            'o:created' => $this->getDateTime($this->created()),
            'o:modified' => $modified ? $this->getDateTime($modified) : null,
            'o:name' => $this->name(),
            'o:query' => $this->query(),
        ];
    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');
        return $url(
            'admin/site/slug/faceted-browse',
            [
                'site-slug' => $this->site()->slug(),
                'id' => $this->id(),
                'action' => $action,
            ],
            ['force_canonical' => $canonical]
        );
    }

    public function owner()
    {
        return $this->getAdapter('users')->getRepresentation($this->resource->getOwner());
    }

    public function site()
    {
        return $this->getAdapter('sites')->getRepresentation($this->resource->getSite());
    }

    public function created()
    {
        return $this->resource->getCreated();
    }

    public function modified()
    {
        return $this->resource->getModified();
    }

    public function name()
    {
        return $this->resource->getName();
    }

    public function query()
    {
        return $this->resource->getQuery();
    }

    public function facets()
    {
        $facets = [];
        foreach ($this->resource->getFacets() as $facet) {
            $facets[] = new FacetedBrowseFacetRepresentation($facet, $this->getServiceLocator());
        }
        return $facets;
    }
}
