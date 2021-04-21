<?php
namespace FacetedBrowse\FacetType;

use Laminas\Form\Element as LaminasElement;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Form\Element as OmekaElement;

class ResourceClass implements FacetTypeInterface
{
    protected $formElements;

    public function __construct(ServiceLocatorInterface $formElements)
    {
        $this->formElements = $formElements;
    }

    public function getLabel() : string
    {
        return 'Class'; // @translate
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-data-form/resource_class.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
        // Class IDs
        $classIds = $this->formElements->get(OmekaElement\ResourceClassSelect::class);
        $classIds->setName('class_ids');
        $classIds->setValue($data['class_ids'] ?? []);
        $classIds->setOptions([
            'label' => 'Classes', // @translate
            'empty_option' => '',
        ]);
        $classIds->setAttributes([
            'id' => 'resource-class-class-ids',
            'data-placeholder' => 'Select classes…', // @translate
            'multiple' => true,
        ]);
        return $view->partial('common/faceted-browse/facet-data-form/resource-class', [
            'elementClassIds' => $classIds,
        ]);
    }
}
