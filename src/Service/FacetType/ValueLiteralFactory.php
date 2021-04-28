<?php
namespace FacetedBrowse\Service\FacetType;

use FacetedBrowse\FacetType\ValueLiteral;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ValueLiteralFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new ValueLiteral($services->get('FormElementManager'));
    }
}
