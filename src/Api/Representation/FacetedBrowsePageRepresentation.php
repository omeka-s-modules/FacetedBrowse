<?php
namespace FacetedBrowse\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class FacetedBrowsePageRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLdType()
    {
        return 'o-module-faceted_browse:Page';
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
            'o:title' => $this->title(),
            'o-module-faceted_browse:resource_type' => $this->resourceType(),
            'o-module-faceted_browse:thumbnail_type' => $this->thumbnailType(),
        ];
    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');
        return $url(
            'admin/site/slug/faceted-browse-page-id',
            [
                'site-slug' => $this->site()->slug(),
                'controller' => 'page',
                'action' => $action,
                'page-id' => $this->id(),
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

    public function title()
    {
        return $this->resource->getTitle();
    }

    public function resourceType()
    {
        return $this->resource->getResourceType();
    }

    public function categories()
    {
        $categories = [];
        $adapter = $this->getAdapter('faceted_browse_categories');
        foreach ($this->resource->getCategories() as $categoryEntity) {
            $categories[] = $adapter->getRepresentation($categoryEntity);
        }
        return $categories;
    }

    public function thumbnailType()
    {
        return $this->resource->getThumbnailType();
    }
}
