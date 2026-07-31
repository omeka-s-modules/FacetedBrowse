<?php
namespace FacetedBrowse\Controller\SiteAdmin;

use FacetedBrowse\Form;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;

class CategoryController extends AbstractActionController
{
    /**
     * Maximum rows in a "show all available values" table. Applied in the facet
     * type's query, so ordering happens before truncation.
     */
    const SHOW_ALL_LIMIT = 1000;

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
            'sort_by_value_options' => $this->facetedBrowse()->getSortByValueOptions(null),
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
        $view->setVariable('page', $page);
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
        $view->setVariable('page', $category->page());
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

    /**
     * Render the "show all available values" table for any facet type.
     *
     * A facet type participates by defining:
     *
     *     public function getShowAllValues(array $options): array
     *
     * $options keys:
     *   'resource_type'         string  items|item_sets|media, from the page; for a
     *                                   facet type querying the API, not DQL
     *   'resource_entity_class' string  the matching Omeka entity class
     *   'resource_ids'          array   IDs matching the category query, never
     *                                   empty; bind it, do not interpolate
     *   'data'                  array   this facet's configured data
     *   'sort_by'               string  'label' or 'has_count'
     *   'sort_order'            string  'asc' or 'desc'
     *   'limit'                 int     apply in the query, not to the result
     *
     * Each row must contain 'label' (string) and 'has_count' (int), plus 'id' if
     * this facet type's "Add all" populates a multi-select. It may also define
     * getShowAllDefaultSort(): ?array returning [column, 'asc'|'desc'].
     *
     * Detected with is_callable, not an interface: naming a FacetedBrowse symbol
     * would fatal on older installs of this module. Not method_exists, which is
     * true for a protected method and would then fatal on call.
     */
    public function showAllValuesAction()
    {
        $page = $this->facetedBrowse()->getRepresentation($this->params('page-id'));
        if (!$page) {
            return $this->getShowAllErrorViewModel(
                $this->translate('Cannot show all. The page could not be found.') // @translate
            );
        }

        $facetTypeName = $this->params()->fromQuery('facet_type');
        $facetTypes = $this->facetedBrowse()->getFacetTypes();
        if (!$facetTypeName || !$facetTypes->has($facetTypeName)) {
            return $this->getShowAllErrorViewModel(
                $this->translate('Cannot show all. Unknown facet type.') // @translate
            );
        }
        $facetType = $facetTypes->get($facetTypeName);

        // The facet type comes from the request, so check before calling.
        if (!is_callable([$facetType, 'getShowAllValues'])) {
            return $this->getShowAllErrorViewModel(
                $this->translate('Cannot show all. The module providing this facet type needs to be updated.') // @translate
            );
        }

        $sort = $this->getShowAllSort($facetType);
        $resourceType = $page->resourceType();
        $resourceIds = $this->facetedBrowse()->getCategoryResourceIds($resourceType, $this->getCategoryQuery());

        $rows = $facetType->getShowAllValues([
            'resource_type' => $resourceType,
            'resource_entity_class' => $this->facetedBrowse()->getResourceEntityClass($resourceType),
            // Doctrine cannot calculate IN() against an empty array.
            'resource_ids' => $resourceIds ?: [0],
            'data' => $this->params()->fromQuery('facet_data', []),
            'sort_by' => $sort['sort_by'],
            'sort_order' => $sort['sort_order'],
            'limit' => self::SHOW_ALL_LIMIT,
        ]);

        foreach ($rows as $row) {
            if (!is_array($row) || !array_key_exists('label', $row) || !array_key_exists('has_count', $row)) {
                return $this->getShowAllErrorViewModel(sprintf(
                    $this->translate('Cannot show all. The "%s" facet type returned a row without a label or count.'), // @translate
                    $facetTypeName
                ));
            }
        }

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('faceted-browse/site-admin/category/show-all-table');
        $view->setVariable('rows', $rows);
        $view->setVariable('sortBy', $sort['sort_by']);
        $view->setVariable('sortOrder', $sort['sort_order']);
        return $view;
    }

    /**
     * Resolve the sort for a show-all request.
     *
     * The request wins when it names a valid column, then the facet type's own
     * preference, then the commonest values first.
     */
    protected function getShowAllSort($facetType)
    {
        $sortBy = $this->params()->fromQuery('sort_by');
        $sortOrder = $this->params()->fromQuery('sort_order');
        if (!in_array($sortBy, ['label', 'has_count'], true)) {
            $sortBy = null;
            $sortOrder = null;
            if (is_callable([$facetType, 'getShowAllDefaultSort'])) {
                $default = $facetType->getShowAllDefaultSort();
                if (is_array($default) && in_array($default[0] ?? null, ['label', 'has_count'], true)) {
                    $sortBy = $default[0];
                    $sortOrder = $default[1] ?? null;
                }
            }
        }
        return [
            'sort_by' => $sortBy ?? 'has_count',
            'sort_order' => in_array($sortOrder, ['asc', 'desc'], true) ? $sortOrder : ('label' === $sortBy ? 'asc' : 'desc'),
        ];
    }

    /**
     * Render a message in place of the show-all table.
     *
     * Never an empty table: a blank result is indistinguishable from data that
     * legitimately has no rows.
     */
    protected function getShowAllErrorViewModel($message)
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('faceted-browse/site-admin/category/show-all-error');
        $view->setVariable('message', $message);
        return $view;
    }

    protected function getCategoryQuery()
    {
        $categoryQuery = $this->params()->fromQuery('category_query');
        parse_str($categoryQuery, $categoryQuery);
        $categoryQuery['site_id'] = $this->currentSite()->id();
        return $categoryQuery;
    }

}
