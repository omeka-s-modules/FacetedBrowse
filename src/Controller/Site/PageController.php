<?php
namespace FacetedBrowse\Controller\Site;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class PageController extends AbstractActionController
{
    public function indexAction()
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
        $this->setBrowseDefaults('created');
        $query = array_merge(
            $this->params()->fromQuery(),
            ['site_id' => $this->currentSite()->id()]
        );
        $response = $this->api()->search('items', $query);
        $this->paginator($response->getTotalResults(), $this->params()->fromQuery('page'));
        $items = $response->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('items', $items);
        return $view;
    }
}
