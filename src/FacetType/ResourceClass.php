<?php
namespace FacetedBrowse\FacetType;

use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
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
        return 'Resource class'; // @translate
    }

    public function getMaxFacets() : ?int
    {
        return null;
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-data-form/resource-class.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
        // Select type
        $selectType = $this->formElements->get(LaminasElement\Select::class);
        $selectType->setName('select_type');
        $selectType->setOptions([
            'label' => 'Select type', // @translate
            'info' => 'Select the select type. For the "single" select type, users may choose only one class at a time via a list or dropdown menu. For the "multiple" select type, users may choose any number of classes at a time via a list.', // @translate
            'value_options' => [
                'single_list' => 'Single (list)', // @translate
                'multiple_list' => 'Multiple (list)', // @translate
                'single_select' => 'Single (dropdown menu)', // @translate
            ],
        ]);
        $selectType->setAttributes([
            'id' => 'resource-class-select-type',
            'value' => $data['select_type'] ?? 'single_list',
        ]);
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
            'elementSelectType' => $selectType,
            'elementClassIds' => $classIds,
        ]);
    }

    public function prepareFacet(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-render/resource-class.js', 'FacetedBrowse'));
    }

    public function renderFacet(PhpRenderer $view, FacetedBrowseFacetRepresentation $facet) : string
    {
        $classes = [];
        $classIds = $facet->data('class_ids', []);
        foreach ($classIds as $classId) {
            $class = $view->api()->read('resource_classes', $classId)->getContent();
            $classes[] = $class;
        }

        $singleSelect = null;
        if ('single_select' === $facet->data('select_type')) {
            // Prepare "Single select" select type.
            $valueOptions = [];
            foreach ($classes as $class) {
                $valueOptions[] = [
                    'value' => $class->id(),
                    'label' => $class->label(),
                    'attributes' => [],
                ];
            }
            $singleSelect = $this->formElements->get(LaminasElement\Select::class);
            $singleSelect->setName(sprintf('resource_class_%s', $facet->id()));
            $singleSelect->setValueOptions($valueOptions);
            $singleSelect->setEmptyOption('Select one…');
            $singleSelect->setAttribute('class', 'resource-class');
            $singleSelect->setAttribute('style', 'width: 90%;');
        }

        return $view->partial('common/faceted-browse/facet-render/resource-class', [
            'facet' => $facet,
            'classes' => $classes,
            'singleSelect' => $singleSelect,
        ]);
    }
}
