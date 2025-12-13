<?php
namespace FacetedBrowse\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use FacetedBrowse\ControllerPlugin\FacetedBrowse;
use Zend\ServiceManager\Factory\FactoryInterface;

class FacetedBrowseFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        return new FacetedBrowse($services);
    }
}
