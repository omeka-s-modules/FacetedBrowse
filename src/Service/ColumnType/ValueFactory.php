<?php
namespace FacetedBrowse\Service\ColumnType;

use FacetedBrowse\ColumnType\Value;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ValueFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new Value($services->get('FormElementManager'));
    }
}
