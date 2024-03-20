<?php
namespace FacetedBrowse\Site\BlockLayout;

use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Entity\SitePageBlock;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Omeka\Stdlib\ErrorStore;

class FacetedBrowsePreview extends AbstractBlockLayout
{
    public function __construct()
    {}

    public function getLabel()
    {
        return 'Faceted browse preview'; // @translate
    }

    public function form(PhpRenderer $view, SiteRepresentation $site, SitePageRepresentation $page = null, SitePageBlockRepresentation $block = null
    ) {
        // Get all pages of this site.
        $pages = $view->api()->search('faceted_browse_pages', ['site_id' => $site->id()])->getContent();

        // Build the value options.
        $valueOptions = [];
        foreach ($pages as $page) {
            $valueOptions[$page->id()] = [
                'label' => $page->title(),
                'options' => [],
            ];
            foreach ($page->categories() as $category) {
                $valueOptions[$page->id()]['options'][$category->id()] = $category->name();
            }
        }

        // Build the form.
        $form = new \Laminas\Form\Form;
        $form->add([
            'type' => 'select',
            'name' => 'o:block[__blockIndex__][o:data][category_id]',
            'options' => [
                'label' => 'Page category', // @translate
                'empty_option' => 'Select one…', // @translate
                'value_options' => $valueOptions,
            ],
        ]);
        $form->add([
            'type' => 'number',
            'name' => 'o:block[__blockIndex__][o:data][limit]',
            'options' => [
                'label' => 'Limit', // @translate
            ],
        ]);
        $form->add([
            'type' => 'text',
            'name' => 'o:block[__blockIndex__][o:data][heading]',
            'options' => [
                'label' => 'Preview title', // @translate
            ],
        ]);
        $form->add([
            'type' => 'text',
            'name' => 'o:block[__blockIndex__][o:data][link_out_text]',
            'options' => [
                'label' => 'Link text', // @translate
            ],
        ]);
        $blockData = $this->getBlockData($block);
        $form->setData([
            'o:block[__blockIndex__][o:data][category_id]' => $blockData['category_id'],
            'o:block[__blockIndex__][o:data][limit]' => $blockData['limit'],
            'o:block[__blockIndex__][o:data][heading]' => $blockData['heading'],
            'o:block[__blockIndex__][o:data][link_out_text]' => $blockData['link_out_text'],
        ]);

        // Render the form.
        return $view->formCollection($form);
    }

    public function render(PhpRenderer $view, SitePageBlockRepresentation $block, $templateViewScript = 'common/faceted-browse/block-layout/faceted-browse-preview')
    {
        $site = $block->page()->site();

        // Get the category.
        $blockData = $this->getBlockData($block);
        $category = $view->api()->searchOne('faceted_browse_categories', ['id' => $blockData['category_id'], 'site_id' => $site->id()])->getContent();

        if (!$category) {
            return ''; // Category does not exist.
        }

        $page = $category->page();
        $columns = $category->columns();

        // Get the items.
        parse_str($category->query(), $query);
        $query['site_id'] = $site->id();
        $query['sort_by'] = $category->sortBy();
        $query['sort_order'] = $category->sortOrder();
        $query['limit'] = 10;
        $resourceType = $page->resourceType();
        $items = $view->api()->search($resourceType, $query)->getContent();

        // Build the heading.
        $heading = null;
        if ($blockData['heading']) {
            $heading = sprintf('<h2>%s</h2>', $view->escapeHtml($blockData['heading']));
        }

        // Build the link out.
        $linkOut = null;
        if ($blockData['link_out_text']) {
            $fragment = [
                'categoryId' => $category->id(),
                'categoryQuery' => $category->query(),
                'sortBy' => $category->sortBy(),
                'sortOrder' => $category->sortOrder(),
                'page' => 1,
                'facetStates' => [],
                'facetQueries' => [],
            ];
            $url = $view->url('site/faceted-browse', ['page-id' => $page->id(), 'action' => 'page'], ['fragment' => json_encode($fragment)], true);
            $linkOut = $view->hyperlink($blockData['link_out_text'], $url, ['class' => 'button']);
        }

        // Render the block.
        return $view->partial($templateViewScript, [
            'columns' => $columns,
            'items' => $items,
            'heading' => $heading,
            'linkOut' => $linkOut,
        ]);
    }

    public function getBlockData(?SitePageBlockRepresentation $block)
    {
        $defaultBlockData = [
            'category_id' => null,
            'limit' => 12,
            'heading' => null,
            'link_out_text' => 'Browse all',
        ];
        $blockData = $block ? $block->data() : [];
        $blockData = array_merge($defaultBlockData, $blockData);
        return $blockData;
    }
}
