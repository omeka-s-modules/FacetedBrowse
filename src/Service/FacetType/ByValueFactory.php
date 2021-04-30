<?php
namespace FacetedBrowse\Service\FacetType;

use FacetedBrowse\FacetType\ByValue;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ByValueFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new ByValue($services->get('FormElementManager'));
    }
}
