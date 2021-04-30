<?php
namespace FacetedBrowse\Service\FacetType;

use FacetedBrowse\FacetType\ByTemplate;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ByTemplateFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new ByTemplate($services->get('FormElementManager'));
    }
}
