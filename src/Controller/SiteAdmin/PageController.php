<?php
namespace FacetedBrowse\Controller\SiteAdmin;

use FacetedBrowse\Form;
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

    public function addAction()
    {
        $form = $this->getForm(Form\PageForm::class);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $formData = $form->getData();
                $formData['o:site'] = ['o:id' => $this->currentSite()->id()];
                $formData['o-module-faceted_browse:category'] = $postData['category'] ?? [];
                $response = $this->api($form)->create('faceted_browse_pages', $formData);
                if ($response) {
                    $category = $response->getContent();
                    $this->messenger()->addSuccess('Successfully added the page.'); // @translate
                    if (isset($postData['submit_save_remain'])) {
                        return $this->redirect()->toRoute('admin/site/slug/faceted-browse/id', ['action' => 'edit', 'id' => $category->id()], true);
                    } else {
                        return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'browse'], true);
                    }
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $categories = $this->api()->search('faceted_browse_categories', [
            'site_id' => $this->currentSite()->id(),
        ])->getContent();

        $view = new ViewModel;
        $view->setVariable('page', null);
        $view->setVariable('categories', $categories);
        $view->setVariable('form', $form);
        return $view;
    }

    public function editAction()
    {
        $page = $this->api()->read('faceted_browse_pages', $this->params('id'))->getContent();

        $form = $this->getForm(Form\PageForm::class);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $formData = $form->getData();
                $formData['o:site'] = ['o:id' => $this->currentSite()->id()];
                $formData['o-module-faceted_browse:category'] = $postData['category'] ?? [];
                $response = $this->api($form)->update('faceted_browse_pages', $page->id(), $formData);
                if ($response) {
                    $this->messenger()->addSuccess('Successfully edited the page.'); // @translate
                    if (isset($postData['submit_save_remain'])) {
                        return $this->redirect()->toRoute('admin/site/slug/faceted-browse/id', ['action' => 'edit'], true);
                    } else {
                        return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'browse'], true);
                    }
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        } else {
            $data = $page->getJsonLd();
            $form->setData($data);
        }

        $categories = $this->api()->search('faceted_browse_categories', [
            'site_id' => $this->currentSite()->id(),
        ])->getContent();

        $view = new ViewModel;
        $view->setVariable('page', $page);
        $view->setVariable('categories', $categories);
        $view->setVariable('form', $form);
        return $view;
    }
}
