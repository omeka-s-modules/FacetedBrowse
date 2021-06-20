<?php
namespace FacetedBrowse\FacetType;

use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
use Laminas\Form\Element as LaminasElement;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Form\Element as OmekaElement;

class ItemSet implements FacetTypeInterface
{
    protected $formElements;

    public function __construct(ServiceLocatorInterface $formElements)
    {
        $this->formElements = $formElements;
    }

    public function getLabel() : string
    {
        return 'Item set'; // @translate
    }

    public function getResourceTypes() : array
    {
        return ['items'];
    }

    public function getMaxFacets() : ?int
    {
        return null;
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-data-form/item-set.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
        // Select type
        $selectType = $this->formElements->get(LaminasElement\Select::class);
        $selectType->setName('select_type');
        $selectType->setOptions([
            'label' => 'Select type', // @translate
            'info' => 'Select the select type. For the "single" select type, users may choose only one item set at a time via a list or dropdown menu. For the "multiple" select type, users may choose any number of item sets at a time via a list.', // @translate
            'value_options' => [
                'single_list' => 'Single (list)', // @translate
                'multiple_list' => 'Multiple (list)', // @translate
                'single_select' => 'Single (dropdown menu)', // @translate
            ],
        ]);
        $selectType->setAttributes([
            'id' => 'item-set-select-type',
            'value' => $data['select_type'] ?? 'single_list',
        ]);
        // Item set IDs
        $itemSetIds = $this->formElements->get(OmekaElement\ItemSetSelect::class);
        $itemSetIds->setName('item_set_ids');
        $itemSetIds->setValue($data['item_set_ids'] ?? []);
        $itemSetIds->setOptions([
            'label' => 'Item sets', // @translate
            'empty_option' => '',
        ]);
        $itemSetIds->setAttributes([
            'id' => 'item-set-item-set-ids',
            'data-placeholder' => 'Select item sets…', // @translate
            'multiple' => true,
        ]);
        return $view->partial('common/faceted-browse/facet-data-form/item-set', [
            'elementSelectType' => $selectType,
            'elementItemSetIds' => $itemSetIds,
        ]);
    }

    public function prepareFacet(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-render/item-set.js', 'FacetedBrowse'));
    }

    public function renderFacet(PhpRenderer $view, FacetedBrowseFacetRepresentation $facet) : string
    {
        $itemSets = [];
        $itemSetIds = $facet->data('item_set_ids', []);
        foreach ($itemSetIds as $itemSetId) {
            $itemSet = $view->api()->read('item_sets', $itemSetId)->getContent();
            $itemSets[] = $itemSet;
        }

        $singleSelect = null;
        if ('single_select' === $facet->data('select_type')) {
            // Prepare "Single select" select type.
            $valueOptions = [];
            foreach ($itemSets as $itemSet) {
                $valueOptions[] = [
                    'value' => $itemSet->id(),
                    'label' => $itemSet->title(),
                    'attributes' => [],
                ];
            }
            $singleSelect = $this->formElements->get(LaminasElement\Select::class);
            $singleSelect->setName(sprintf('item_set_%s', $facet->id()));
            $singleSelect->setValueOptions($valueOptions);
            $singleSelect->setEmptyOption('Select one…');
            $singleSelect->setAttribute('class', 'item-set');
            $singleSelect->setAttribute('style', 'width: 90%;');
        }

        return $view->partial('common/faceted-browse/facet-render/item-set', [
            'facet' => $facet,
            'itemSets' => $itemSets,
            'singleSelect' => $singleSelect,
        ]);
    }
}
