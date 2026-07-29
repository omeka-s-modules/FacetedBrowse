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
            'o-module-faceted_browse:page' => $this->page()->getReference(),
            'o:created' => $this->getDateTime($this->created()),
            'o:modified' => $modified ? $this->getDateTime($modified) : null,
            'o:name' => $this->name(),
            'o:query' => $this->query(),
            'o-module-faceted_browse:sort_by' => $this->sortBy(),
            'o-module-faceted_browse:sort_order' => $this->sortOrder(),
            'o-module-faceted_browse:facet' => $this->facets(),
            'o-module-faceted_browse:column' => $this->columns(),
            'o-module-faceted_browse:helper_text' => $this->helperText(),
            'o-module-faceted_browse:helper_text_button_label' => $this->helperTextButtonLabel(),
            'o-module-faceted_browse:value_facet_mode' => $this->valueFacetMode(),
        ];
    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');
        return $url(
            'admin/site/slug/faceted-browse-category-id',
            [
                'site-slug' => $this->site()->slug(),
                'controller' => 'category',
                'action' => $action,
                'page-id' => $this->page()->id(),
                'category-id' => $this->id(),
            ],
            ['force_canonical' => $canonical]
        );
    }

    /**
     * The category lives in the URL fragment, so server-side this is
     * indistinguishable from its page's URL.
     */
    public function siteUrl($siteSlug = null, $canonical = false)
    {
        // Mirrors resetState() in asset/js/faceted-browse.js. initState()
        // discards the whole state if any key is missing.
        $fragment = json_encode([
            'categoryId' => $this->id(),
            'sortBy' => null,
            'sortOrder' => null,
            'page' => null,
            'facetStates' => [],
            'facetQueries' => [],
        ]);
        return sprintf(
            '%s#%s',
            $this->page()->siteUrl($siteSlug, $canonical),
            rawurlencode($fragment)
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

    public function sortBy()
    {
        return $this->resource->getSortBy();
    }

    public function sortOrder()
    {
        return $this->resource->getSortOrder();
    }

    public function helperText()
    {
        return $this->resource->getHelperText();
    }

    public function helperTextButtonLabel()
    {
        return $this->resource->getHelperTextButtonLabel();
    }

    public function valueFacetMode()
    {
        return $this->resource->getValueFacetMode();
    }

    public function page()
    {
        return $this->getAdapter('faceted_browse_pages')->getRepresentation($this->resource->getPage());
    }

    public function position()
    {
        return $this->resource->getPosition();
    }

    public function facets()
    {
        $facets = [];
        foreach ($this->resource->getFacets() as $facet) {
            $facets[] = new FacetedBrowseFacetRepresentation($facet, $this->getServiceLocator());
        }
        return $facets;
    }

    public function columns()
    {
        $columns = [];
        foreach ($this->resource->getColumns() as $column) {
            $columns[] = new FacetedBrowseColumnRepresentation($column, $this->getServiceLocator());
        }
        return $columns;
    }
}
