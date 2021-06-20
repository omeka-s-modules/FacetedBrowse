<?php
namespace FacetedBrowse\Controller\SiteAdmin;

use Laminas\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute('admin/site/slug/faceted-browse-page', ['action' => 'browse'], true);
    }
}
