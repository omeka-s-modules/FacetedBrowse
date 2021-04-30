<?php
namespace FacetedBrowse\ControllerPlugin;

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
     * Get all available values and their counts of a property.
     *
     * @param int $propertyId
     * @param string $queryType
     * @param array $categoryQuery
     * @return array
     */
    public function getByValueValues($propertyId, $queryType, array $categoryQuery)
    {
        $api = $this->services->get('Omeka\ApiManager');
        $em = $this->services->get('Omeka\EntityManager');

        // Get the IDs of all items that satisfy the category query.
        $ids = $api->search('items', $categoryQuery, ['returnScalar' => 'id'])->getContent();

        $qb = $em->createQueryBuilder();
        $qb->from('Omeka\Entity\Value', 'v')
            ->andWhere($qb->expr()->in('v.resource', $ids))
            ->groupBy('value')
            ->orderBy('value_count', 'DESC')
            ->addOrderBy('value', 'ASC');
        switch ($queryType) {
            case 'res':
                $qb->select("CONCAT(vr.id, ' ', vr.title) value", 'COUNT(v) value_count')
                    ->join('v.valueResource', 'vr')
                    ->andWhere('v.type = :type')
                    ->setParameter('type', 'resource');
                break;
            case 'ex':
                $qb->select("CONCAT(p.id, ' ', vo.label, ': ', p.label) value", 'COUNT(v) value_count')
                    ->join('v.property', 'p')
                    ->join('p.vocabulary', 'vo');
                break;
            case 'eq':
            case 'in':
            default:
                $qb->select('v.value value', 'COUNT(v.value) value_count')
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
    public function getByClassClasses(array $query)
    {
        $api = $this->services->get('Omeka\ApiManager');
        $em = $this->services->get('Omeka\EntityManager');

        // Get the IDs of all items that satisfy the category query.
        $itemIds = $api->search('items', $query, ['returnScalar' => 'id'])->getContent();

        $dql = '
        SELECT CONCAT(v.label, \': \', rc.label) label, COUNT(i.id) item_count
        FROM Omeka\Entity\Item i
        JOIN i.resourceClass rc
        JOIN rc.vocabulary v
        WHERE i.id IN (:itemIds)
        GROUP BY rc.id
        ORDER BY item_count DESC';
        $query = $em->createQuery($dql)
            ->setParameter('itemIds', $itemIds);
        return $query->getResult();
    }

    /**
     * Get all available templates and their counts.
     *
     * @param arry $query
     * @return array
     */
    public function getByTemplateTemplates(array $query)
    {
        $api = $this->services->get('Omeka\ApiManager');
        $em = $this->services->get('Omeka\EntityManager');

        // Get the IDs of all items that satisfy the category query.
        $itemIds = $api->search('items', $query, ['returnScalar' => 'id'])->getContent();

        $dql = '
        SELECT rt.label label, COUNT(i.id) item_count
        FROM Omeka\Entity\Item i
        JOIN i.resourceTemplate rt
        WHERE i.id IN (:itemIds)
        GROUP BY rt.id
        ORDER BY item_count DESC';
        $query = $em->createQuery($dql)
            ->setParameter('itemIds', $itemIds);
        return $query->getResult();
    }
}
