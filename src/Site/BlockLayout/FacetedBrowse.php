<?php
namespace FacetedBrowse\Site\BlockLayout;

use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\View\Renderer\PhpRenderer;

class FacetedBrowse extends AbstractBlockLayout
{
    public function getLabel()
    {
        return 'Faceted browse'; // @translate
    }

    public function form(PhpRenderer $view, SiteRepresentation $site,
        SitePageRepresentation $page = null, SitePageBlockRepresentation $block = null
    ) {
        $data = $block ? $block->data() : ['page_id' => ''];
        $valueOptions = [];
        $pages = $view->api()->search('faceted_browse_pages', ['site_id' => $site->id()])->getContent();
        foreach ($pages as $page) {
            $valueOptions[$page->id()] = $page->title();
        }
        $element = new Element\Select('o:block[__blockIndex__][o:data][page_id]');
        $element->setValueOptions($valueOptions)
            ->setEmptyOption($view->translate('Select a page'))
            ->setOptions([
                'label' => $view->translate('Page'),
            ])
            ->setAttributes([
                'required' => true,
            ])
            ->setValue($data['page_id']);
        return $view->formRow($element);
    }

    public function render(PhpRenderer $view, SitePageBlockRepresentation $block)
    {
        $data = $block ? $block->data() : ['page_id' => ''];
        $page = $view->api()->read('faceted_browse_pages', $data['page_id'])->getContent();

        return $view->partial('faceted-browse/site/page/page', ['page' => $page]);
    }
}
