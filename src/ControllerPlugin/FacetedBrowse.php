<?php
namespace FacetedBrowse\ControllerPlugin;

use FacetedBrowse\Api\Representation\FacetedBrowseCategoryRepresentation;
use Omeka\Api\Exception\NotFoundException;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;

class FacetedBrowse extends AbstractPlugin
{
    protected $services;

    public function __construct(ServiceLocatorInterface $services)
    {
        $this->services = $services;
    }

    /**
     * Get a FacetedBrowse representation.
     *
     * Provides a single method to get a FacetedBrowse page or category record
     * representation. Used primarily to ensure that the route is valid.
     *
     * @param int $pageId
     * @param int|null $categoryId
     * @return FacetedBrowsePageRepresentation|FacetedBrowseCategoryRepresentation
     */
    public function getRepresentation($pageId, $categoryId = null)
    {
        $controller = $this->getController();
        if ($categoryId) {
            try {
                $category = $controller->api()->read('faceted_browse_categories', $categoryId)->getContent();
            } catch (NotFoundException $e) {
                return false;
            }
            $page = $category->page();
            return ($pageId == $page->id()) ? $category : false;
        }
        try {
            $page = $controller->api()->read('faceted_browse_pages', $pageId)->getContent();
        } catch (NotFoundException $e) {
            return false;
        }
        return $page;
    }

    /**
     * Get the facet type manager.
     *
     * @return FacetedBrowse\FacetType\Manager
     */
    public function getFacetTypes()
    {
        return $this->services->get('FacetedBrowse\FacetTypeManager');
    }

    /**
     * Get the column type manager.
     *
     * @return FacetedBrowse\ColumnType\Manager
     */
    public function getColumnTypes()
    {
        return $this->services->get('FacetedBrowse\ColumnTypeManager');
    }

    /**
     * Get the sortings for a browse page.
     *
     * @param ?FacetedBrowseCategoryRepresentation $category
     * @return array
     */
    public function getSortings(?FacetedBrowseCategoryRepresentation $category)
    {
        $controller = $this->getController();
        $sortConfig = $this->services->get('Omeka\Browse')->getSortConfig('public', 'items');
        if ($category) {
            // Get sortings for a category.
            foreach ($category->columns() as $column) {
                if ($column->excludeSortBy()) {
                    // Don't include sorting if it was excluded.
                    continue;
                }
                $sortBy = $column->sortBy();
                if ($sortBy) {
                    $sortConfig[$column->sortBy()] = $controller->translate($column->name());
                }
            }
        }
        $sortings = [];
        foreach ($sortConfig as $sortKey => $sortValue) {
            $sortings[] = [
                'label' => $sortValue,
                'value' => $sortKey,
            ];
        }
        return $sortings;
    }

    /**
     * Get the value options for a sort by select element.
     *
     * @param ?FacetedBrowseCategoryRepresentation $category
     * @return array
     */
    public function getSortByValueOptions(?FacetedBrowseCategoryRepresentation $category = null)
    {
        $sortByValueOptions = [];
        foreach ($this->getSortings($category) as $sorting) {
            $sortByValueOptions[$sorting['value']] = $sorting['label'];
        }
        return $sortByValueOptions;
    }

    /**
     * Get the IDs of all resources that satisfy the query.
     *
     * @param string $resourceType
     * @param array $categoryQuery
     * @return array
     */
    public function getCategoryResourceIds($resourceType, array $categoryQuery)
    {
        $api = $this->services->get('Omeka\ApiManager');
        return $api->search($resourceType, $categoryQuery, ['returnScalar' => 'id'])->getContent();
    }

    /**
     * Get the corresponding entity class of a resource.
     *
     * @param string $resourceType
     * @return string
     */
    public function getResourceEntityClass($resourceType)
    {
        switch ($resourceType) {
            case 'media':
                return 'Omeka\Entity\Media';
            case 'item_sets':
                return 'Omeka\Entity\ItemSet';
            case 'items':
            default:
                return 'Omeka\Entity\Item';
        }
    }
}
