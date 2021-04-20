<?php
namespace FacetedBrowse\Service\Form;

use FacetedBrowse\Form\PageForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class PageFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new PageForm(null, $options);
    }
}
