<?php
namespace FacetedBrowse\Service\Site\NavigationLink;

use FacetedBrowse\Site\NavigationLink\FacetedBrowse;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FacetedBrowseFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new FacetedBrowse($services->get('Omeka\ApiManager'));
    }
}
