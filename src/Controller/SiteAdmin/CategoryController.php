<?php
namespace FacetedBrowse\Controller\SiteAdmin;

use FacetedBrowse\Form;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;

class CategoryController extends AbstractActionController
{
    public function addAction()
    {
        $page = $this->facetedBrowse()->getRepresentation($this->params('page-id'));
        if (!$page) {
            return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'index'], true);
        }

        $form = $this->getForm(Form\CategoryForm::class, [
            'site' => $this->currentSite(),
            'facet_types' => $this->facetedBrowse()->getFacetTypes(),
            'column_types' => $this->facetedBrowse()->getColumnTypes(),
            'page' => $page,
        ]);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $formData = $form->getData();
                $formData['o:site'] = ['o:id' => $this->currentSite()->id()];
                $formData['o-module-faceted_browse:page'] = ['o:id' => $page->id()];
                $formData['o-module-faceted_browse:facet'] = $postData['o-module-faceted_browse:facet'] ?? [];
                $formData['o-module-faceted_browse:column'] = $postData['o-module-faceted_browse:column'] ?? [];
                $response = $this->api($form)->create('faceted_browse_categories', $formData);
                if ($response) {
                    $category = $response->getContent();
                    $this->messenger()->addSuccess('Category successfully added.'); // @translate
                    if (isset($postData['submit_save_remain'])) {
                        return $this->redirect()->toRoute('admin/site/slug/faceted-browse-category-id', ['action' => 'edit', 'category-id' => $category->id()], true);
                    } else {
                        return $this->redirect()->toRoute('admin/site/slug/faceted-browse-page-id', ['action' => 'edit'], true);
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
        $category = $this->facetedBrowse()->getRepresentation(
            $this->params('page-id'),
            $this->params('category-id')
        );
        if (!$category) {
            return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'index'], true);
        }

        $sortByValueOptions = [];
        foreach ($this->facetedBrowse()->getSortings($category) as $sorting) {
            $sortByValueOptions[$sorting['value']] = $sorting['label'];
        }

        $form = $this->getForm(Form\CategoryForm::class, [
            'site' => $this->currentSite(),
            'facet_types' => $this->facetedBrowse()->getFacetTypes(),
            'column_types' => $this->facetedBrowse()->getColumnTypes(),
            'sort_by_value_options' => $this->facetedBrowse()->getSortByValueOptions($category),
            'category' => $category,
            'page' => $category->page(),
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
                    $this->messenger()->addSuccess('Category successfully edited.'); // @translate
                    if (isset($postData['submit_save_remain'])) {
                        return $this->redirect()->toRoute('admin/site/slug/faceted-browse-category-id', ['action' => 'edit'], true);
                    } else {
                        return $this->redirect()->toRoute('admin/site/slug/faceted-browse-page-id', ['action' => 'edit'], true);
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
            $category = $this->facetedBrowse()->getRepresentation(
                $this->params('page-id'),
                $this->params('category-id')
            );
            if (!$category) {
                return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'index'], true);
            }
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('faceted_browse_categories', $category->id());
                if ($response) {
                    $this->messenger()->addSuccess('Category successfully deleted.'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute('admin/site/slug/faceted-browse-page-id', ['action' => 'edit'], true);
    }

    public function facetFormAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'index'], true);
        }

        $facetType = $this->params()->fromPost('facet_type');
        $facetName = $this->params()->fromPost('facet_name');
        $facetData = json_decode((string) $this->params()->fromPost('facet_data'), true);
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
            return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'index'], true);
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
            return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'index'], true);
        }

        $columnType = $this->params()->fromPost('column_type');
        $columnName = $this->params()->fromPost('column_name');
        $columnExcludeSortBy = $this->params()->fromPost('column_exclude_sort_by');
        $columnData = json_decode((string) $this->params()->fromPost('column_data'), true);
        if (!is_array($columnData)) {
            $columnData = [];
        }

        $form = $this->getForm(Form\ColumnForm::class);
        $form->setData([
            'column_type' => $columnType,
            'column_name' => $columnName,
            'column_exclude_sort_by' => $columnExcludeSortBy,
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
            return $this->redirect()->toRoute('admin/site/slug/faceted-browse', ['action' => 'index'], true);
        }
        $column = [
            'o:id' => null,
            'o:name' => $this->params()->fromPost('column_name'),
            'o-module-faceted_browse:type' => $this->params()->fromPost('column_type'),
            'o-module-faceted_browse:exclude_sort_by' => $this->params()->fromPost('column_exclude_sort_by'),
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
        $page = $this->facetedBrowse()->getRepresentation($this->params('page-id'));
        $values = $this->facetedBrowse()->getValueValues(
            $page->resourceType(),
            $this->params()->fromQuery('property_id'),
            $this->params()->fromQuery('query_type'),
            $this->getCategoryQuery()
        );
        return $this->getViewModel($values);
    }

    public function resourceClassClassesAction()
    {
        $page = $this->facetedBrowse()->getRepresentation($this->params('page-id'));
        $classes = $this->facetedBrowse()->getResourceClassClasses(
            $page->resourceType(),
            $this->getCategoryQuery()
        );
        return $this->getViewModel($classes);
    }

    public function resourceTemplateTemplatesAction()
    {
        $page = $this->facetedBrowse()->getRepresentation($this->params('page-id'));
        $templates = $this->facetedBrowse()->getResourceTemplateTemplates(
            $page->resourceType(),
            $this->getCategoryQuery()
        );
        return $this->getViewModel($templates);
    }

    public function itemSetItemSetsAction()
    {
        $page = $this->facetedBrowse()->getRepresentation($this->params('page-id'));
        $itemSets = $this->facetedBrowse()->getItemSetItemSets(
            $page->resourceType(),
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
