<?php
namespace FacetedBrowse\ControllerPlugin;

use FacetedBrowse\Api\Representation\FacetedBrowseCategoryRepresentation;
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
     * @param int $propertyId
     * @param string $queryType
     * @param array $categoryQuery
     * @return array
     */
    public function getValueValues($propertyId, $queryType, array $categoryQuery)
    {
        $em = $this->services->get('Omeka\EntityManager');
        $qb = $em->createQueryBuilder();
        // Cannot use an empty array to calculate IN(). It results in a Doctrine
        // QueryException. Instead, use an array containing one nonexistent ID.
        $itemIds = $this->getCategoryItemIds($categoryQuery) ?: [0];
        $qb->from('Omeka\Entity\Value', 'v')
            ->andWhere($qb->expr()->in('v.resource', $itemIds))
            ->groupBy('label')
            ->orderBy('has_count', 'DESC')
            ->addOrderBy('label', 'ASC');
        switch ($queryType) {
            case 'res':
            case 'nres':
                $qb->select("CONCAT(vr.id, ' ', vr.title) label", 'COUNT(v) has_count')
                    ->join('v.valueResource', 'vr')
                    ->andWhere('v.type = :type')
                    ->setParameter('type', 'resource');
                break;
            case 'ex':
            case 'nex':
                $qb->select("CONCAT(p.id, ' ', vo.label, ': ', p.label) label", 'COUNT(v) has_count')
                    ->join('v.property', 'p')
                    ->join('p.vocabulary', 'vo');
                break;
            case 'eq':
            case 'neq':
            case 'in':
            case 'nin':
            default:
                $qb->select('v.value label', 'COUNT(v.value) has_count')
                    ->andWhere('v.type = :type')
                    ->setParameter('type', 'literal');
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
     * @param arry $query
     * @return array
     */
    public function getResourceClassClasses(array $categoryQuery)
    {
        $em = $this->services->get('Omeka\EntityManager');
        $dql = '
        SELECT CONCAT(v.label, \': \', rc.label) label, COUNT(i.id) has_count
        FROM Omeka\Entity\Item i
        JOIN i.resourceClass rc
        JOIN rc.vocabulary v
        WHERE i.id IN (:itemIds)
        GROUP BY rc.id
        ORDER BY has_count DESC';
        $query = $em->createQuery($dql)
            ->setParameter('itemIds', $this->getCategoryItemIds($categoryQuery));
        return $query->getResult();
    }

    /**
     * Get all available templates and their counts.
     *
     * @param arry $categoryQuery
     * @return array
     */
    public function getResourceTemplateTemplates(array $categoryQuery)
    {
        $em = $this->services->get('Omeka\EntityManager');
        $dql = '
        SELECT rt.label label, COUNT(i.id) has_count
        FROM Omeka\Entity\Item i
        JOIN i.resourceTemplate rt
        WHERE i.id IN (:itemIds)
        GROUP BY rt.id
        ORDER BY has_count DESC';
        $query = $em->createQuery($dql)
            ->setParameter('itemIds', $this->getCategoryItemIds($categoryQuery));
        return $query->getResult();
    }

    /**
     * Get all available item sets and their counts.
     *
     * @param arry $query
     * @return array
     */
    public function getItemSetItemSets(array $categoryQuery)
    {
        $em = $this->services->get('Omeka\EntityManager');
        $dql = '
        SELECT iset.title label, COUNT(i.id) has_count
        FROM Omeka\Entity\Item i
        JOIN i.itemSets iset
        WHERE i.id IN (:itemIds)
        GROUP BY iset.id
        ORDER BY has_count DESC';
        $query = $em->createQuery($dql)
            ->setParameter('itemIds', $this->getCategoryItemIds($categoryQuery));
        return $query->getResult();
    }

    /**
     * Get the IDs of all items that satisfy the query.
     *
     * @param array $categoryQuery
     * @return array
     */
    protected function getCategoryItemIds(array $categoryQuery)
    {
        $api = $this->services->get('Omeka\ApiManager');
        return $api->search('items', $categoryQuery, ['returnScalar' => 'id'])->getContent();
    }
}
