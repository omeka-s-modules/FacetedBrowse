<?php
namespace FacetedBrowse\Service\FacetType;

use FacetedBrowse\FacetType\ByClass;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ByClassFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new ByClass($services->get('FormElementManager'));
    }
}
