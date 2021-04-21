<?php
namespace FacetedBrowse\Service\FacetType;

use FacetedBrowse\FacetType\ResourceClass;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ResourceClassFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new ResourceClass($services->get('FormElementManager'));
    }
}
