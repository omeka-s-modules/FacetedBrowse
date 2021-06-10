<?php
namespace FacetedBrowse\Service\ColumnType;

use FacetedBrowse\ColumnType\ResourceClass;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ResourceClassFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new ResourceClass($services->get('FormElementManager'));
    }
}
