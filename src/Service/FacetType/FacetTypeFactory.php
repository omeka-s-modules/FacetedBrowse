<?php
namespace FacetedBrowse\Service\FacetType;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FacetTypeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        // Convert underscored requested_name into camel cased ClassName.
        $classParts = array_map(function ($requestedNamePart) {
            return ucfirst($requestedNamePart);
        }, explode('_', $requestedName));
        $class = sprintf('FacetedBrowse\FacetType\%s', implode('', $classParts));
        return new $class($services->get('FormElementManager'));
    }
}
