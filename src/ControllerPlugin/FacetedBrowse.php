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

    public function getFacetTypes()
    {
        return $this->services->get('FacetedBrowse\FacetTypeManager');
    }

    public function getFacetType($facetType)
    {
        return $this->services->get('FacetedBrowse\FacetTypeManager')->get($facetType);
    }

    public function getPropertyLiteralValues($propertyId, $query)
    {
        $api = $this->services->get('Omeka\ApiManager');
        $em = $this->services->get('Omeka\EntityManager');

        // Get the IDs of all items that satisfy the category query.
        parse_str($query, $query);
        $ids = $api->search('items', $query, ['returnScalar' => 'id'])->getContent();

        // Get all unique literal values of the specified property of the
        // specified items.
        $dql = '
        SELECT DISTINCT(v.value) value
        FROM Omeka\Entity\Value v
        WHERE v.type = :type
        AND v.property = :propertyId
        AND v.resource IN (:ids)
        ORDER BY v.value';
        $query = $em->createQuery($dql)
            ->setParameter('type', 'literal')
            ->setParameter('propertyId', $propertyId)
            ->setParameter('ids', $ids);
        return array_column($query->getResult(), 'value');
    }
}
