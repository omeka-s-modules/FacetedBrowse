<?php
namespace FacetedBrowse\Controller\SiteAdmin;

use FacetedBrowse\Form;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;
use Omeka\Stdlib\Message;

class PageController extends AbstractActionController
{
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
        $form = $this->getForm(Form\PageForm::class, [
            'page' => null,
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
                    $this->messenger()->addSuccess('Page successfully added. Add categories below.'); // @translate
                    if (isset($postData['submit_save_remain'])) {
                        return $this->redirect()->toRoute('admin/site/slug/faceted-browse-page-id', ['action' => 'edit', 'page-id' => $category->id()], true);
                    } else {
                        return $this->redirect()->toRoute('admin/site/slug/faceted-browse-page', ['action' => 'browse'], true);
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
        $page = $this->facetedBrowse()->getRepresentation($this->params('page-id'));
        if (!$page) {
            return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'index'], true);
        }

        $form = $this->getForm(Form\PageForm::class, [
            'page' => $page,
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
                    $message = new Message(
                        'Page successfully edited. %s', // @translate
                        sprintf(
                            '<a href="%s">%s</a>',
                            htmlspecialchars($this->url()->fromRoute('admin/site/slug/action', ['controller' => 'index', 'action' => 'navigation'], true)),
                            $this->translate('Add it to site navigation?')
                        )
                    );
                    $message->setEscapeHtml(false);
                    $this->messenger()->addSuccess($message);
                    if (isset($postData['submit_save_remain'])) {
                        return $this->redirect()->toRoute('admin/site/slug/faceted-browse-page-id', ['action' => 'edit'], true);
                    } else {
                        return $this->redirect()->toRoute('admin/site/slug/faceted-browse-page', ['action' => 'browse'], true);
                    }
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        } else {
            $data = $page->getJsonLd();
            $form->setData($data);
        }

        $view = new ViewModel;
        $view->setVariable('page', $page);
        $view->setVariable('form', $form);
        return $view;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $page = $this->facetedBrowse()->getRepresentation($this->params('page-id'));
            if (!$page) {
                return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'index'], true);
            }
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('faceted_browse_pages', $page->id());
                if ($response) {
                    $this->messenger()->addSuccess('Page successfully deleted.'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute('admin/site/slug/faceted-browse-page', ['action' => 'browse'], true);
    }
}
