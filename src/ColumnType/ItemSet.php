<?php
namespace FacetedBrowse\ColumnType;

use FacetedBrowse\Api\Representation\FacetedBrowseColumnRepresentation;
use Laminas\Form\Element as LaminasElement;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class ItemSet implements ColumnTypeInterface
{
    protected $formElements;

    public function __construct(ServiceLocatorInterface $formElements)
    {
        $this->formElements = $formElements;
    }

    public function getLabel(): string
    {
        return 'Item set'; // @translate
    }

    public function getResourceTypes(): array
    {
        return ['items'];
    }

    public function getMaxColumns(): ?int
    {
        return 1;
    }

    public function prepareDataForm(PhpRenderer $view): void
    {
        $view->headScript()->appendFile($view->assetUrl('js/column-data-form/item-set.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data): string
    {
        $maxItemSetsInput = $this->formElements->get(LaminasElement\Number::class);
        $maxItemSetsInput->setName('max_item_sets');
        $maxItemSetsInput->setOptions([
            'label' => 'Max item sets', // @translate
            'info' => 'Enter the maximum number of item sets to display. Set to blank to display all item sets.', // @translate
        ]);
        $maxItemSetsInput->setAttributes([
            'id' => 'item-set-max-item-sets',
            'value' => $data['max_item_sets'] ?? 1,
            'min' => 1,
            'step' => 1,
        ]);

        return $view->partial('common/faceted-browse/column-data-form/item-set', [
            'maxItemSetsInput' => $maxItemSetsInput,
        ]);
    }

    public function getSortBy(FacetedBrowseColumnRepresentation $column): ?string
    {
        // Omeka does not provide a way to sort by item set title.
        return null;
    }

    public function renderContent(FacetedBrowseColumnRepresentation $column, AbstractResourceEntityRepresentation $resource): string
    {
        $maxItemSets = $column->data('max_item_sets');

        // Get the item sets.
        $itemSets = $resource->itemSets();
        if ($maxItemSets) {
            $itemSets = array_slice($itemSets, 0, $maxItemSets);
        }

        // Prepare the content.
        $content = '<ul>';
        foreach ($itemSets as $itemSet) {
            $content .= sprintf('<li>%s</li>', $itemSet->linkPretty());
        }
        $content .= '</ul>';
        return $content;
    }
}
