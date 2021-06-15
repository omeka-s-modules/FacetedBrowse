<?php
namespace FacetedBrowse\Controller\SiteAdmin;

use FacetedBrowse\Form;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;
use Omeka\Stdlib\Message;

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
            'column_types' => $this->facetedBrowse()->getColumnTypes(),
        ]);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $formData = $form->getData();
                $formData['o:site'] = ['o:id' => $this->currentSite()->id()];
                $formData['o-module-faceted_browse:facet'] = $postData['o-module-faceted_browse:facet'] ?? [];
                $formData['o-module-faceted_browse:column'] = $postData['o-module-faceted_browse:column'] ?? [];
                $response = $this->api($form)->create('faceted_browse_categories', $formData);
                if ($response) {
                    $category = $response->getContent();
                    $message = new Message(
                        'Category successfully added. %s', // @translate
                        sprintf(
                            '<a href="%s">%s</a>',
                            htmlspecialchars($this->url()->fromRoute('admin/site/slug/faceted-browse', ['controller' => 'page', 'action' => 'browse'], true)),
                            $this->translate('Assign it to a page?')
                        )
                    );
                    $message->setEscapeHtml(false);
                    $this->messenger()->addSuccess($message);
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
            'column_types' => $this->facetedBrowse()->getColumnTypes(),
            'category' => $category,
        ]);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $formData = $form->getData();
                $formData['o:site'] = ['o:id' => $this->currentSite()->id()];
                $formData['o-module-faceted_browse:facet'] = $postData['o-module-faceted_browse:facet'] ?? [];
                $formData['o-module-faceted_browse:column'] = $postData['o-module-faceted_browse:column'] ?? [];
                $response = $this->api($form)->update('faceted_browse_categories', $category->id(), $formData);
                if ($response) {
                    $message = new Message(
                        'Category successfully edited. %s', // @translate
                        sprintf(
                            '<a href="%s">%s</a>',
                            htmlspecialchars($this->url()->fromRoute('admin/site/slug/faceted-browse', ['controller' => 'page', 'action' => 'browse'], true)),
                            $this->translate('Assign it to a page?')
                        )
                    );
                    $message->setEscapeHtml(false);
                    $this->messenger()->addSuccess($message);
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

    public function columnFormAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'browse'], true);
        }

        $columnType = $this->params()->fromPost('column_type');
        $columnName = $this->params()->fromPost('column_name');
        $columnData = json_decode($this->params()->fromPost('column_data'), true);
        if (!is_array($columnData)) {
            $columnData = [];
        }

        $form = $this->getForm(Form\ColumnForm::class);
        $form->setData([
            'column_type' => $columnType,
            'column_name' => $columnName,
        ]);

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('form', $form);
        $view->setVariable('columnType', $columnType);
        $view->setVariable('columnData', $columnData);
        return $view;
    }

    public function columnRowAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'browse'], true);
        }
        $column = [
            'o:id' => null,
            'o:name' => $this->params()->fromPost('column_name'),
            'o-module-faceted_browse:type' => $this->params()->fromPost('column_type'),
            'o:data' => [],
        ];
        $index = $this->params()->fromPost('index');

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('column', $column);
        $view->setVariable('index', $index);
        return $view;
    }

    public function valueValuesAction()
    {
        $values = $this->facetedBrowse()->getValueValues(
            $this->params()->fromQuery('property_id'),
            $this->params()->fromQuery('query_type'),
            $this->getCategoryQuery()
        );
        return $this->getViewModel($values);
    }

    public function resourceClassClassesAction()
    {
        $classes = $this->facetedBrowse()->getResourceClassClasses(
            $this->getCategoryQuery()
        );
        return $this->getViewModel($classes);
    }

    public function resourceTemplateTemplatesAction()
    {
        $templates = $this->facetedBrowse()->getResourceTemplateTemplates(
            $this->getCategoryQuery()
        );
        return $this->getViewModel($templates);
    }

    public function itemSetItemSetsAction()
    {
        $itemSets = $this->facetedBrowse()->getItemSetItemSets(
            $this->getCategoryQuery()
        );
        return $this->getViewModel($itemSets);
    }

    protected function getCategoryQuery()
    {
        $categoryQuery = $this->params()->fromQuery('category_query');
        parse_str($categoryQuery, $categoryQuery);
        $categoryQuery['site_id'] = $this->currentSite()->id();
        return $categoryQuery;
    }

    protected function getViewModel($rows)
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('faceted-browse/site-admin/category/show-all-table');
        $view->setVariable('rows', $rows);
        return $view;
    }
}
