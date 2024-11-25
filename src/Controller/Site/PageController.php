<?php
namespace FacetedBrowse\Controller\Site;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class PageController extends AbstractActionController
{
    public function pageAction()
    {
        $pageId = $this->params('page-id');
        $page = $this->api()->read('faceted_browse_pages', $pageId)->getContent();

        $view = new ViewModel;
        $view->setVariable('page', $page);
        return $view;
    }

    public function categoriesAction()
    {
        $pageId = $this->params('page-id');
        $page = $this->api()->read('faceted_browse_pages', $pageId)->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('page', $page);
        return $view;
    }

    public function facetsAction()
    {
        $categoryId = $this->params()->fromQuery('category_id');
        $category = $this->api()->read('faceted_browse_categories', $categoryId)->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('category', $category);
        return $view;
    }

    public function browseAction()
    {
        $pageId = $this->params('page-id');
        $page = $this->api()->read('faceted_browse_pages', $pageId)->getContent();

        $categoryId = $this->params()->fromQuery('faceted_browse_category_id');
        $category = $categoryId ? $this->api()->read('faceted_browse_categories', $categoryId)->getContent() : null;

        $columns = $category ? $category->columns() : null;

        // Set default sort.
        $sortByValueOptions = $this->facetedBrowse()->getSortByValueOptions($category);
        $sortBy = array_key_first($sortByValueOptions);
        if ($category) {
            $sortBy = array_key_exists($category->sortBy(), $sortByValueOptions)
                ? $category->sortBy()
                : array_key_first($sortByValueOptions);
        }
        $sortOrder = 'desc';
        if ($category) {
            $sortOrder = in_array($category->sortOrder(), ['desc', 'asc']) ? $category->sortOrder() : 'desc';
        }
        $this->setBrowseDefaults($sortBy, $sortOrder);

        // Get the IDs of all resources in this category.
        $categoryResourceIds = null;
        if ($category) {
            parse_str($category->query(), $categoryQuery);
            $categoryResourceIds = $this->api()
                ->search($page->resourceType(), $categoryQuery, ['returnScalar' => 'id'])
                ->getContent();
        }

        // Get the resources from the facets query (only those within this category).
        $query = array_merge(
            $this->params()->fromQuery(),
            ['id' => $categoryResourceIds],
            ['site_id' => $this->currentSite()->id()]
        );
        $response = $this->api()->search($page->resourceType(), $query);
        $this->paginator($response->getTotalResults(), $this->params()->fromQuery('page'));
        $items = $response->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('items', $items);
        $view->setVariable('query', $query);
        $view->setVariable('columns', $columns);
        $view->setVariable('sortings', $this->facetedBrowse()->getSortings($category));
        return $view;
    }
}
