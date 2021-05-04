<?php
namespace FacetedBrowse\FacetType;

use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Form\Element as OmekaElement;

class ByClass implements FacetTypeInterface
{
    protected $formElements;

    public function __construct(ServiceLocatorInterface $formElements)
    {
        $this->formElements = $formElements;
    }

    public function getLabel() : string
    {
        return 'By class'; // @translate
    }

    public function getMaxFacets() : ?int
    {
        return null;
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-data-form/by-class.js', 'FacetedBrowse'));
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
            'id' => 'by-class-class-ids',
            'data-placeholder' => 'Select classes…', // @translate
            'multiple' => true,
        ]);
        return $view->partial('common/faceted-browse/facet-data-form/by-class', [
            'elementClassIds' => $classIds,
        ]);
    }

    public function prepareFacet(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-render/by-class.js', 'FacetedBrowse'));
    }

    public function renderFacet(PhpRenderer $view, FacetedBrowseFacetRepresentation $facet) : string
    {
        $classes = [];
        $classIds = $facet->data('class_ids', []);
        foreach ($classIds as $classId) {
            $class = $view->api()->read('resource_classes', $classId)->getContent();
            $classes[] = $class;
        }
        return $view->partial('common/faceted-browse/facet-render/by-class', [
            'facet' => $facet,
            'classes' => $classes,
        ]);
    }
}
