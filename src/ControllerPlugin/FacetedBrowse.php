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
        $sortings = [];
        if ($category) {
            foreach ($category->columns() as $column) {
                $sortBy = $column->sortBy();
                if ($sortBy) {
                    $sortings[] = [
                        'label' => $controller->translate($column->name()),
                        'value' => $column->sortBy(),
                    ];
                }
            }
        }
        if (!$sortings) {
            $sortings = [
                [
                    'label' => $controller->translate('Created'),
                    'value' => 'created',
                ],
                [
                    'label' => $controller->translate('Title'),
                    'value' => 'title',
                ],
            ];
        }
        return $sortings;
    }

    /**
     * Get all available values and their counts of a property.
     *
     * @param string $resourceType
     * @param int $propertyId
     * @param string $queryType
     * @param array $categoryQuery
     * @return array
     */
    public function getValueValues($resourceType, $propertyId, $queryType, array $categoryQuery)
    {
        $em = $this->services->get('Omeka\EntityManager');
        $qb = $em->createQueryBuilder();
        // Cannot use an empty array to calculate IN(). It results in a Doctrine
        // QueryException. Instead, use an array containing one nonexistent ID.
        $itemIds = $this->getCategoryResourceIds($resourceType, $categoryQuery) ?: [0];
        $qb->from('Omeka\Entity\Value', 'v')
            ->andWhere($qb->expr()->in('v.resource', $itemIds))
            ->groupBy('label')
            ->orderBy('has_count', 'DESC')
            ->addOrderBy('label', 'ASC');
        switch ($queryType) {
            case 'res':
            case 'nres':
                $qb->select("0 id, CONCAT(vr.id, ' ', vr.title) label", 'COUNT(v) has_count')
                    ->join('v.valueResource', 'vr')
                    ->andWhere('v.type = :type')
                    ->setParameter('type', 'resource');
                break;
            case 'ex':
            case 'nex':
                $qb->select("0 id, CONCAT(p.id, ' ', vo.label, ': ', p.label) label", 'COUNT(v) has_count')
                    ->join('v.property', 'p')
                    ->join('p.vocabulary', 'vo');
                break;
            case 'eq':
            case 'neq':
            case 'in':
            case 'nin':
            default:
                $qb->select('0 id, v.value label', 'COUNT(v.value) has_count');
        }
        if ($propertyId) {
            $qb->andWhere('v.property = :propertyId')
                ->setParameter('propertyId', $propertyId);
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * Get all available classes and their counts.
     *
     * @param string $resourceType
     * @param arry $query
     * @return array
     */
    public function getResourceClassClasses($resourceType, array $categoryQuery)
    {
        $em = $this->services->get('Omeka\EntityManager');
        $dql = sprintf('
        SELECT rc.id id, CONCAT(v.label, \': \', rc.label) label, COUNT(r.id) has_count
        FROM %s r
        JOIN r.resourceClass rc
        JOIN rc.vocabulary v
        WHERE r.id IN (:resourceIds)
        GROUP BY rc.id
        ORDER BY has_count DESC', $this->getResourceEntityClass($resourceType));
        $query = $em->createQuery($dql)
            ->setParameter('resourceIds', $this->getCategoryResourceIds($resourceType, $categoryQuery));
        return $query->getResult();
    }

    /**
     * Get all available templates and their counts.
     *
     * @param string $resourceType
     * @param arry $categoryQuery
     * @return array
     */
    public function getResourceTemplateTemplates($resourceType, array $categoryQuery)
    {
        $em = $this->services->get('Omeka\EntityManager');
        $dql = sprintf('
        SELECT rt.id id, rt.label label, COUNT(r.id) has_count
        FROM %s r
        JOIN r.resourceTemplate rt
        WHERE r.id IN (:resourceIds)
        GROUP BY rt.id
        ORDER BY has_count DESC', $this->getResourceEntityClass($resourceType));
        $query = $em->createQuery($dql)
            ->setParameter('resourceIds', $this->getCategoryResourceIds($resourceType, $categoryQuery));
        return $query->getResult();
    }

    /**
     * Get all available item sets and their counts.
     *
     * @param string $resourceType
     * @param arry $query
     * @return array
     */
    public function getItemSetItemSets($resourceType, array $categoryQuery)
    {
        $em = $this->services->get('Omeka\EntityManager');
        $dql = sprintf('
        SELECT iset.id id, iset.title label, COUNT(r.id) has_count
        FROM %s r
        JOIN r.itemSets iset
        WHERE r.id IN (:resourceIds)
        GROUP BY iset.id
        ORDER BY has_count DESC', $this->getResourceEntityClass($resourceType));
        $query = $em->createQuery($dql)
            ->setParameter('resourceIds', $this->getCategoryResourceIds($resourceType, $categoryQuery));
        return $query->getResult();
    }

    /**
     * Get the IDs of all resources that satisfy the query.
     *
     * @param string $resourceType
     * @param array $categoryQuery
     * @return array
     */
    protected function getCategoryResourceIds($resourceType, array $categoryQuery)
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
    protected function getResourceEntityClass($resourceType)
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
