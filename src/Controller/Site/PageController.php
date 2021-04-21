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
}
