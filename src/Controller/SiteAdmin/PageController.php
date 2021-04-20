<?php
namespace FacetedBrowse\Controller\SiteAdmin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class PageController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['controller' => 'page', 'action' => 'browse'], true);
    }

    public function browseAction()
    {
        $this->setBrowseDefaults('created');
        $response = $this->api()->search('faceted_browse_pages', $this->params()->fromQuery());
        $this->paginator($response->getTotalResults(), $this->params()->fromQuery('page'));
        $pages = $response->getContent();

        $view = new ViewModel;
        $view->setVariable('pages', $pages);
        return $view;
    }
}
