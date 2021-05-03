<?php
namespace FacetedBrowse\Controller\SiteAdmin;

use FacetedBrowse\Form;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;

class PageController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['controller' => 'page', 'action' => 'browse'], true);
    }

    public function browseAction()
    {
        $this->setBrowseDefaults('created');
        $query = array_merge(
            $this->params()->fromQuery(),
            ['site_id' => $this->currentSite()->id()]
        );
        $response = $this->api()->search('faceted_browse_pages', $query);
        $this->paginator($response->getTotalResults(), $this->params()->fromQuery('page'));
        $pages = $response->getContent();

        $view = new ViewModel;
        $view->setVariable('pages', $pages);
        return $view;
    }

    public function addAction()
    {
        $categories = $this->api()->search('faceted_browse_categories', [
            'site_id' => $this->currentSite()->id(),
        ])->getContent();
        $form = $this->getForm(Form\PageForm::class, [
            'categories' => $categories,
        ]);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $formData = $form->getData();
                $formData['o:site'] = ['o:id' => $this->currentSite()->id()];
                $formData['o-module-faceted_browse:category'] = $postData['o-module-faceted_browse:category'] ?? [];
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

        $view = new ViewModel;
        $view->setVariable('page', null);
        $view->setVariable('form', $form);
        return $view;
    }

    public function editAction()
    {
        $page = $this->api()->read('faceted_browse_pages', $this->params('id'))->getContent();
        $categories = $this->api()->search('faceted_browse_categories', [
            'site_id' => $this->currentSite()->id(),
        ])->getContent();

        $form = $this->getForm(Form\PageForm::class, [
            'categories' => $categories,
        ]);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $formData = $form->getData();
                $formData['o:site'] = ['o:id' => $this->currentSite()->id()];
                $formData['o-module-faceted_browse:category'] = $postData['o-module-faceted_browse:category'] ?? [];
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
        $view->setVariable('form', $form);
        return $view;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $category = $this->api()->read('faceted_browse_pages', $this->params('id'))->getContent();
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('faceted_browse_pages', $category->id());
                if ($response) {
                    $this->messenger()->addSuccess('Successfully deleted the page.'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'browse'], true);
    }

    public function categoryRowAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'browse'], true);
        }
        $categoryId = $this->params()->fromPost('category_id');
        $category = $this->api()->read('faceted_browse_categories', $categoryId)->getContent();
        $category = [
            'o:id' => $category->id(),
            'o:name' => $category->name(),
        ];
        $index = $this->params()->fromPost('index');

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('category', $category);
        $view->setVariable('index', $index);
        return $view;
    }
}
