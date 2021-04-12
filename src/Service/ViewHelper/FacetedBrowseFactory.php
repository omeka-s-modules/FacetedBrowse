<?php
namespace FacetedBrowse\Service\ViewHelper;

use FacetedBrowse\ViewHelper\FacetedBrowse;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FacetedBrowseFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new FacetedBrowse($services);
    }
}
