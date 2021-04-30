<?php
namespace FacetedBrowse\Service\FacetType;

use FacetedBrowse\FacetType\ByItemSet;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ByItemSetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new ByItemSet($services->get('FormElementManager'));
    }
}
