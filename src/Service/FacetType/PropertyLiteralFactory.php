<?php
namespace FacetedBrowse\Service\FacetType;

use FacetedBrowse\FacetType\PropertyLiteral;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class PropertyLiteralFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new PropertyLiteral($services->get('FormElementManager'));
    }
}
