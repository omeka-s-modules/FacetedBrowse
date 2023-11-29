<?php
namespace FacetedBrowse\Site\NavigationLink;

use Omeka\Api\Exception\NotFoundException;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Site\Navigation\Link\LinkInterface;
use Omeka\Stdlib\ErrorStore;

class FacetedBrowse implements LinkInterface
{
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
        if (!isset($data['label']) || '' === trim($data['page_id'])) {
            $errorStore->addError('o:navigation', 'Invalid navigation: Faceted browse missing a label');
            return false;
        }
        if (!isset($data['page_id']) || !is_numeric($data['page_id'])) {
            $errorStore->addError('o:navigation', 'Invalid navigation: Faceted browse missing a page');
            return false;
        }
        return true;
    }

    public function getLabel(array $data, SiteRepresentation $site)
    {
        if (isset($data['label']) && '' !== trim($data['label'])) {
            return $data['label'];
        }
        $services = $site->getServiceLocator();
        $api = $services->get('Omeka\ApiManager');
        $translator = $services->get('MvcTranslator');
        try {
            $page = $api->read('faceted_browse_pages', $data['page_id']);
        } catch (NotFoundException $e) {
            return $translator->translate('[Missing Page]'); // @translate
        }
        return $page->getContent()->title();
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
            'label' => $data['label'],
            'page_id' => $data['page_id'],
        ];
    }
}
