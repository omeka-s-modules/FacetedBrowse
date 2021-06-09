<?php
namespace FacetedBrowse\Service\ColumnType;

use FacetedBrowse\ColumnType\Title;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class TitleFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new Title($services->get('FormElementManager'));
    }
}
