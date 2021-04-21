<?php
namespace FacetedBrowse\Site\NavigationLink;

use Omeka\Api\Exception\NotFoundException;
use Omeka\Api\Manager;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Site\Navigation\Link\LinkInterface;
use Omeka\Stdlib\ErrorStore;

class FacetedBrowse implements LinkInterface
{
    protected $api;

    public function __construct(Manager $api)
    {
        $this->api = $api;
    }

    public function getName()
    {
        return 'Faceted browse'; // @translate
    }

    public function getFormTemplate()
    {
        return 'common/faceted-browse/navigation-link-form/faceted-browse';
    }

    public function isValid(array $data, ErrorStore $errorStore)
    {
        if (!(isset($data['page_id']) && is_numeric($data['page_id']))) {
            $errorStore->addError('o:navigation', 'Invalid navigation: Faceted browse missing page');
            return false;
        }
        return true;
    }

    public function getLabel(array $data, SiteRepresentation $site)
    {
        $label = null;
        if (isset($data['page_id'])) {
            try {
                $page = $this->api->read('faceted_browse_pages', $data['page_id'])->getContent();
                $label = $page->title();
            } catch (NotFoundException $e) {
                $label = null;
            }
        }
        return $label;
    }

    public function toZend(array $data, SiteRepresentation $site)
    {
        return [
            'route' => 'site/faceted-browse',
            'params' => [
                'site-slug' => $site->slug(),
                'page-id' => $data['page_id'],
            ],
        ];
    }

    public function toJstree(array $data, SiteRepresentation $site)
    {
        return [
            'page_id' => $data['page_id'],
        ];
    }
}
