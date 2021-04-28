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
     * @param arry $query
     * @return array
     */
    public function getValueLiteralValues($propertyId, array $query)
    {
        $api = $this->services->get('Omeka\ApiManager');
        $em = $this->services->get('Omeka\EntityManager');

        // Get the IDs of all items that satisfy the category query.
        $ids = $api->search('items', $query, ['returnScalar' => 'id'])->getContent();

        // Get all unique literal values of the specified property of the
        // specified items.
        $dql = '
        SELECT v.value value, COUNT(v.value) value_count
        FROM Omeka\Entity\Value v
        WHERE v.type = :type
        AND v.property = :propertyId
        AND v.resource IN (:ids)
        GROUP BY value
        ORDER BY value_count DESC, value ASC';
        $query = $em->createQuery($dql)
            ->setParameter('type', 'literal')
            ->setParameter('propertyId', $propertyId)
            ->setParameter('ids', $ids);
        return $query->getResult();
    }

    /**
     * Get all available classes and their counts.
     *
     * @param arry $query
     * @return array
     */
    public function getResourceClassClasses(array $query)
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
}
