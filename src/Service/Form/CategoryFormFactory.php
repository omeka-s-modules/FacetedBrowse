<?php
namespace FacetedBrowse\Service\Form;

use FacetedBrowse\Form\CategoryForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class CategoryFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new CategoryForm(null, $options);
    }
}
