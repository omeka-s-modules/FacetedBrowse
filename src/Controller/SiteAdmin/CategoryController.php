<?php
namespace FacetedBrowse\Controller\SiteAdmin;

use FacetedBrowse\Form;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;

class CategoryController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'browse'], true);
    }

    public function browseAction()
    {
        $this->setBrowseDefaults('created');
        $query = array_merge(
            $this->params()->fromQuery(),
            ['site_id' => $this->currentSite()->id()]
        );
        $response = $this->api()->search('faceted_browse_categories', $query);
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
                $formData['o-module-faceted_browse:facet'] = $postData['o-module-faceted_browse:facet'] ?? [];
                $response = $this->api($form)->create('faceted_browse_categories', $formData);
                if ($response) {
                    $category = $response->getContent();
                    $this->messenger()->addSuccess('Successfully added the category.'); // @translate
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
                $formData['o-module-faceted_browse:facet'] = $postData['o-module-faceted_browse:facet'] ?? [];
                $response = $this->api($form)->update('faceted_browse_categories', $category->id(), $formData);
                if ($response) {
                    $this->messenger()->addSuccess('Successfully edited the category.'); // @translate
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
        return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'browse'], true);
    }

    public function facetFormAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'browse'], true);
        }

        $facetType = $this->params()->fromPost('facet_type');
        $facetName = $this->params()->fromPost('facet_name');
        $facetData = json_decode($this->params()->fromPost('facet_data'), true);
        if (!is_array($facetData)) {
            $facetData = [];
        }

        $form = $this->getForm(Form\FacetForm::class);
        $form->setData([
            'facet_type' => $facetType,
            'facet_name' => $facetName,
        ]);

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('form', $form);
        $view->setVariable('facetType', $facetType);
        $view->setVariable('facetData', $facetData);
        return $view;
    }

    public function facetRowAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'browse'], true);
        }
        $facet = [
            'o:id' => null,
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

    public function byValueValuesAction()
    {
        $values = $this->facetedBrowse()->getByValueValues(
            $this->params()->fromQuery('property_id'),
            $this->params()->fromQuery('query_type'),
            $this->getCategoryQuery()
        );
        return $this->getJsonResponse($values);
    }

    public function byClassClassesAction()
    {
        $classes = $this->facetedBrowse()->getByClassClasses(
            $this->getCategoryQuery()
        );
        return $this->getJsonResponse($classes);
    }

    public function byTemplateTemplatesAction()
    {
        $templates = $this->facetedBrowse()->getByTemplateTemplates(
            $this->getCategoryQuery()
        );
        return $this->getJsonResponse($templates);
    }

    public function byItemSetItemSetsAction()
    {
        $itemSets = $this->facetedBrowse()->getByItemSetItemSets(
            $this->getCategoryQuery()
        );
        return $this->getJsonResponse($itemSets);
    }

    protected function getCategoryQuery()
    {
        $categoryQuery = $this->params()->fromQuery('category_query');
        parse_str($categoryQuery, $categoryQuery);
        $categoryQuery['site_id'] = $this->currentSite()->id();
        return $categoryQuery;
    }

    protected function getJsonResponse($content)
    {
        $response = $this->getResponse();
        $responseHeaders = $response->getHeaders();
        $responseHeaders->addHeaderLine('Content-Type: application/json');
        $response->setContent(json_encode($content));
        return $response;
    }
}
