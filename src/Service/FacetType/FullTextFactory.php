<?php
namespace FacetedBrowse\Service\FacetType;

use FacetedBrowse\FacetType\FullText;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FullTextFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new FullText($services->get('FormElementManager'));
    }
}
