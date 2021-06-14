<?php
namespace FacetedBrowse\FacetType;

use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
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
        return $view->partial('common/faceted-browse/facet-render/item-set', [
            'facet' => $facet,
            'itemSets' => $itemSets,
        ]);
    }
}
