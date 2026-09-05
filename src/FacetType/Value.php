<?php
namespace FacetedBrowse\FacetType;

use Doctrine\ORM\EntityManager;
use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
use Laminas\Form\Element as LaminasElement;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Form\Element as OmekaElement;

class Value implements FacetTypeInterface
{
    use ShowAllTrait;

    protected $formElements;

    protected $entityManager;

    public function __construct(ServiceLocatorInterface $formElements, EntityManager $entityManager)
    {
        $this->formElements = $formElements;
        $this->entityManager = $entityManager;
    }

    public function getLabel(): string
    {
        return 'Value'; // @translate
    }

    public function getResourceTypes(): array
    {
        return ['items', 'item_sets', 'media'];
    }

    public function getMaxFacets(): ?int
    {
        return null;
    }

    public function prepareDataForm(PhpRenderer $view): void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-data-form/value.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data): string
    {
        // Property ID
        $propertyId = $this->formElements->get(OmekaElement\PropertySelect::class);
        $propertyId->setName('property_id');
        $propertyId->setOptions([
            'label' => 'Property', // @translate
            'empty_option' => '',
        ]);
        $propertyId->setAttributes([
            'id' => 'value-property-id',
            'value' => $data['property_id'] ?? null,
            'data-placeholder' => '[Any property]', // @translate
        ]);
        // Query type
        $queryType = $this->formElements->get(LaminasElement\Select::class);
        $queryType->setName('query_type');
        $queryType->setOptions([
            'label' => 'Query type', // @translate
            'value_options' => [
                'eq' => 'Is exactly', // @translate
                'neq' => 'Is not exactly', // @translate
                'in' => 'Contains', // @translate
                'nin' => 'Does not contain', // @translate
                'res' => 'Is resource with ID', // @translate
                'nres' => 'Is not resource with ID', // @translate
                'ex' => 'Has any value', // @translate
                'nex' => 'Has no values', // @translate
            ],
        ]);
        $queryType->setAttributes([
            'id' => 'value-query-type',
            'value' => $data['query_type'] ?? 'eq',
        ]);
        // Select type
        $selectType = $this->formElements->get(LaminasElement\Select::class);
        $selectType->setName('select_type');
        $selectType->setOptions([
            'label' => 'Select type', // @translate
            'info' => 'Select the select type. For the "single" select type, users may choose only one value at a time via a list or dropdown menu. For the "multiple" select type, users may choose any number of values at a time via a list. For the special "text input" select type, users may input their own text.', // @translate
            'value_options' => [
                'single_list' => 'Single (list)', // @translate
                'multiple_list' => 'Multiple (list)', // @translate
                'single_select' => 'Single (dropdown menu)', // @translate
                'text_input' => 'Text input', // @translate
            ],
        ]);
        $selectType->setAttributes([
            'id' => 'value-select-type',
            'value' => $data['select_type'] ?? 'single_list',
        ]);
        // Truncate values
        $truncateValues = $this->formElements->get(LaminasElement\Number::class);
        $truncateValues->setName('truncate_values');
        $truncateValues->setOptions([
            'label' => 'Truncate values', // @translate
            'info' => 'Enter the number of values to show on the select list when the page first loads. If the number of values exceeds this number, the remainder will be hidden until the user clicks to show more. Enter nothing to show the entire list at all times.', // @translate
        ]);
        $truncateValues->setAttributes([
            'id' => 'value-truncate-values',
            'value' => $data['truncate_values'] ?? '',
            'min' => 1,
            'step' => 1,
        ]);
        // Values
        $values = $this->formElements->get(LaminasElement\Textarea::class);
        $values->setName('values');
        $values->setOptions([
            'label' => 'Values', // @translate
            'info' => $view->translate('
            <p>Enter the values, separated by new lines. The format of each value depends on the query type:</p>
            <ul>
                <li>"Is exactly": enter a value that is an exact match to the property value.</li>
                <li>"Contains": enter a value that matches any part of the property value.</li>
                <li>"Is resource with ID": enter the resource ID followed by any value (usually the resource title), separated by a single space.</li>
                <li>"Has any value": enter the property ID followed by any value (usually the property label), separated by a single space.</li>
            </ul>'),
            'escape_info' => false,
        ]);
        $values->setAttributes([
            'id' => 'value-values',
            'style' => 'height: 300px;',
            'value' => $data['values'] ?? null,
        ]);

        return $view->partial('common/faceted-browse/facet-data-form/value', [
            'facetType' => $this,
            'elementPropertyId' => $propertyId,
            'elementQueryType' => $queryType,
            'elementSelectType' => $selectType,
            'elementTruncateValues' => $truncateValues,
            'elementValues' => $values,
        ]);
    }

    public function prepareFacet(PhpRenderer $view): void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-render/value.js', 'FacetedBrowse'));
    }

    public function renderFacet(PhpRenderer $view, FacetedBrowseFacetRepresentation $facet): string
    {
        $values = $facet->data('values');
        $values = explode("\n", $values);
        $values = array_map('trim', $values);
        // Drop blank lines, which would otherwise render as an option with no
        // label. Not array_filter() with no callback, which would also drop "0".
        $values = array_filter($values, fn ($value) => '' !== $value);
        $values = array_unique($values);
        switch ($facet->data('query_type')) {
            case 'res':
            case 'nres':
            case 'ex':
            case 'nex':
                $idKeyValues = [];
                foreach ($values as $value) {
                    if (preg_match('/^(\d+) (.+)/', $value, $matches)) {
                        $idKeyValues[$matches[1]] = $matches[2];
                    } elseif (preg_match('/^(\d+)/', $value, $matches)) {
                        $idKeyValues[$matches[1]] = null;
                    }
                }
                $values = $idKeyValues;
                break;
            case 'eq':
            case 'neq':
            case 'in':
            case 'nin':
            default:
                $values = array_combine($values, $values);
        }

        $singleSelect = null;
        if ('single_select' === $facet->data('select_type')) {
            // Prepare "Single select" select type.
            $valueOptions = [];
            foreach ($values as $key => $value) {
                $dataPropertyId = $facet->data('property_id');
                $dataValue = $key;
                if (in_array($facet->data('query_type'), ['ex', 'nex'])) {
                    $dataPropertyId = $key;
                }
                $valueOptions[] = [
                    'value' => $key,
                    'label' => $value,
                    'attributes' => [
                        'data-property-id' => $dataPropertyId,
                        'data-value' => $dataValue,
                    ],
                ];
            }
            $singleSelect = $this->formElements->get(LaminasElement\Select::class);
            $singleSelect->setName(sprintf('value_%s', $facet->id()));
            $singleSelect->setValueOptions($valueOptions);
            $singleSelect->setEmptyOption('Select one…');
            $singleSelect->setAttribute('class', 'value');
            $singleSelect->setAttribute('style', 'width: 90%;');
            $singleSelect->setAttribute('aria-labelledby', sprintf('facet-legend-%s', $facet->id()));
        }

        $textInput = null;
        if ('text_input' === $facet->data('select_type')) {
            $textInput = new LaminasElement\Text(sprintf('value_%s', $facet->id()));
            $textInput->setAttribute('class', 'value');
            $textInput->setAttribute('data-property-id', $facet->data('property_id'));
            $textInput->setAttribute('style', 'width: 90%;');
            $textInput->setAttribute('aria-labelledby', sprintf('facet-legend-%s', $facet->id()));
        }

        return $view->partial('common/faceted-browse/facet-render/value', [
            'facet' => $facet,
            'values' => $values,
            'singleSelect' => $singleSelect,
            'textInput' => $textInput,
        ]);
    }

    /**
     * Return rows for the "show all available values" table.
     *
     * For the "res" and "ex" query types the label keeps an ID prefix, because
     * renderFacet() parses it back out and uses the ID. Sorting therefore orders
     * by the components, not the concatenation, whose leading ID would dominate.
     *
     * @see FacetedBrowse\Controller\SiteAdmin\CategoryController::showAllValuesAction()
     */
    public function getShowAllValues(array $options): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from('Omeka\Entity\Value', 'v')
            ->andWhere('v.resource IN (:resourceIds)')
            ->setParameter('resourceIds', $options['resource_ids'])
            ->setMaxResults($options['limit']);

        // Grouped by the components, not the concatenation, so that ordering by a
        // component is permitted under MySQL's ONLY_FULL_GROUP_BY.
        switch ($options['data']['query_type'] ?? null) {
            case 'res':
            case 'nres':
                // A resource may have no title, and one null argument makes the
                // whole CONCAT null rather than being skipped. "Add all" drops a
                // null label silently, so an untitled resource would vanish from
                // a row the table had shown with a count.
                $qb->select("CONCAT(vr.id, ' ', COALESCE(vr.title, '')) label", 'COUNT(v) has_count')
                    ->join('v.valueResource', 'vr')
                    ->groupBy('vr.id')
                    ->addGroupBy('vr.title');
                $orderBy = ['vr.title'];
                break;
            case 'ex':
            case 'nex':
                $qb->select("CONCAT(p.id, ' ', vo.label, ': ', p.label) label", 'COUNT(v) has_count')
                    ->join('v.property', 'p')
                    ->join('p.vocabulary', 'vo')
                    ->groupBy('p.id')
                    ->addGroupBy('vo.label')
                    ->addGroupBy('p.label');
                $orderBy = ['vo.label', 'p.label'];
                break;
            default:
                // Resource and URI values leave v.value null, and would otherwise
                // group into one blank row counting zero. These query types match
                // on the literal value, so those rows are not values they can use.
                $qb->select('v.value label', 'COUNT(v.value) has_count')
                    ->andWhere('v.value IS NOT NULL')
                    ->groupBy('v.value');
                $orderBy = ['v.value'];
        }

        $this->applyShowAllSort($qb, $options, $orderBy);

        // Applied only when set: a value facet with no property means all
        // properties, unlike the numeric facet types, which require one.
        $propertyId = $options['data']['property_id'] ?? null;
        if ($propertyId) {
            $qb->andWhere('v.property = :propertyId')
                ->setParameter('propertyId', $propertyId);
        }
        // A label of only whitespace has nothing to show and nothing to add, so
        // drop it rather than list a row "Add all" would skip. Not done centrally:
        // a facet type that selects an ID stays usable with a blank label.
        $rows = $qb->getQuery()->getResult();
        return array_values(array_filter($rows, fn ($row) => '' !== trim((string) $row['label'])));
    }
}
