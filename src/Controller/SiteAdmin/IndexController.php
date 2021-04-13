<?php
namespace FacetedBrowse\Controller\SiteAdmin;

use FacetedBrowse\Form;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
    }

    public function browseAction()
    {
        $this->setBrowseDefaults('created');
        $response = $this->api()->search('faceted_browse_categories', $this->params()->fromQuery());
        $this->paginator($response->getTotalResults(), $this->params()->fromQuery('page'));
        $categories = $response->getContent();

        $view = new ViewModel;
        $view->setVariable('categories', $categories);
        return $view;
    }

    public function addAction()
    {
        $form = $this->getForm(Form\CategoryForm::class, [
            'site' => $this->currentSite(),
            'facet_types' => $this->facetedBrowse()->getFacetTypes(),
        ]);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $formData = $form->getData();
                $formData['o:site'] = ['o:id' => $this->currentSite()->id()];
                $formData['o-module-faceted_browse:facet'] = is_array($postData['facet']) ? $postData['facet'] : [];
                $response = $this->api($form)->create('faceted_browse_categories', $formData);
                if ($response) {
                    $category = $response->getContent();
                    $this->messenger()->addSuccess('Successfully added the category.'); // @translate
                    if (isset($postData['submit_save_remain'])) {
                        return $this->redirect()->toRoute(null, ['action' => 'edit', 'id' => $category->id()], true);
                    } else {
                        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
                    }
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('category', null);
        $view->setVariable('form', $form);
        return $view;
    }

    public function editAction()
    {
        $category = $this->api()->read('faceted_browse_categories', $this->params('id'))->getContent();

        $form = $this->getForm(Form\CategoryForm::class, [
            'site' => $this->currentSite(),
            'facet_types' => $this->facetedBrowse()->getFacetTypes(),
            'category' => $category,
        ]);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $formData = $form->getData();
                $formData['o:site'] = ['o:id' => $this->currentSite()->id()];
                $formData['o-module-faceted_browse:facet'] = $postData['facet'] ?? [];
                $response = $this->api($form)->update('faceted_browse_categories', $category->id(), $formData);
                if ($response) {
                    $this->messenger()->addSuccess('Successfully edited the category.'); // @translate
                    if (isset($postData['submit_save_remain'])) {
                        return $this->redirect()->toRoute(null, ['action' => 'edit'], true);
                    } else {
                        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
                    }
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        } else {
            $data = $category->getJsonLd();
            $form->setData($data);
        }

        $view = new ViewModel;
        $view->setVariable('category', $category);
        $view->setVariable('form', $form);
        return $view;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $category = $this->api()->read('faceted_browse_categories', $this->params('id'))->getContent();
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('faceted_browse_categories', $category->id());
                if ($response) {
                    $this->messenger()->addSuccess('Successfully deleted the category.'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
    }

    public function facetFormAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }
        $facet = [
            'facet_name' => $this->params()->fromPost('facet_name'),
            'facet_type' => $this->params()->fromPost('facet_type'),
        ];
        $form = $this->getForm(Form\FacetForm::class);
        $form->setData($facet);

        $facetData = json_decode($this->params()->fromPost('facet_data'), true);
        if (!is_array($facetData)) {
            $facetData = [];
        }
        $facetType = $this->facetedBrowse()->getFacetType($facet['facet_type']);
        $facetType->setDataElements($form->get('facet_data'), $facetData);

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('form', $form);
        $view->setVariable('facetType', $facetType);
        return $view;
    }

    public function facetRowAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }
        $facet = [
            'o:name' => $this->params()->fromPost('facet_name'),
            'o-module-faceted_browse:type' => $this->params()->fromPost('facet_type'),
            'o:data' => [],
        ];
        $index = $this->params()->fromPost('index');

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('facet', $facet);
        $view->setVariable('index', $index);
        return $view;
    }

    public function propertyLiteralValuesAction()
    {
        $propertyId = $this->params()->fromQuery('property_id');
        $query = $this->params()->fromQuery('query');
        parse_str($query, $query);
        $query['site_id'] = $this->currentSite()->id();

        $values = $this->facetedBrowse()->getPropertyLiteralValues($propertyId, $query);

        $response = $this->getResponse();
        $responseHeaders = $response->getHeaders();
        $responseHeaders->addHeaderLine('Content-Type: application/json');
        $response->setContent(json_encode($values));
        return $response;
    }
}
