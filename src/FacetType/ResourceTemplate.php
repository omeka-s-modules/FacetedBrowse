<?php
namespace FacetedBrowse\FacetType;

use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
use Laminas\Form\Element as LaminasElement;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Form\Element as OmekaElement;

class ResourceTemplate implements FacetTypeInterface
{
    protected $formElements;

    public function __construct(ServiceLocatorInterface $formElements)
    {
        $this->formElements = $formElements;
    }

    public function getLabel(): string
    {
        return 'Resource template'; // @translate
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
        $view->headScript()->appendFile($view->assetUrl('js/facet-data-form/resource-template.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data): string
    {
        // Select type
        $selectType = $this->formElements->get(LaminasElement\Select::class);
        $selectType->setName('select_type');
        $selectType->setOptions([
            'label' => 'Select type', // @translate
            'info' => 'Select the select type. For the "single" select type, users may choose only one template at a time via a list or dropdown menu. For the "multiple" select type, users may choose any number of templates at a time via a list.', // @translate
            'value_options' => [
                'single_list' => 'Single (list)', // @translate
                'multiple_list' => 'Multiple (list)', // @translate
                'single_select' => 'Single (dropdown menu)', // @translate
            ],
        ]);
        $selectType->setAttributes([
            'id' => 'resource-template-select-type',
            'value' => $data['select_type'] ?? 'single_list',
        ]);
        // Truncate resource templates.
        $truncateResourceTemplates = $this->formElements->get(LaminasElement\Number::class);
        $truncateResourceTemplates->setName('truncate_resource_templates');
        $truncateResourceTemplates->setOptions([
            'label' => 'Truncate templates', // @translate
            'info' => 'Enter the number of templates to show on the select list when the page first loads. If the number of templates exceeds this number, the remainder will be hidden until the user clicks to show more. Enter nothing to show the entire list at all times.', // @translate
        ]);
        $truncateResourceTemplates->setAttributes([
            'id' => 'resource-template-truncate-resource-templates',
            'value' => $data['truncate_resource_templates'] ?? '',
            'min' => 1,
            'step' => 1,
        ]);
        // Template IDs
        $templateIds = $this->formElements->get(OmekaElement\ResourceTemplateSelect::class);
        $templateIds->setName('template_ids');
        $templateIds->setValue($data['template_ids'] ?? []);
        $templateIds->setOptions([
            'label' => 'Templates', // @translate
            'empty_option' => '',
        ]);
        $templateIds->setAttributes([
            'id' => 'resource-template-template-ids',
            'data-placeholder' => 'Select templates…', // @translate
            'multiple' => true,
        ]);
        return $view->partial('common/faceted-browse/facet-data-form/resource-template', [
            'elementSelectType' => $selectType,
            'elementTruncateResourceTemplates' => $truncateResourceTemplates,
            'elementTemplateIds' => $templateIds,
        ]);
    }

    public function prepareFacet(PhpRenderer $view): void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-render/resource-template.js', 'FacetedBrowse'));
    }

    public function renderFacet(PhpRenderer $view, FacetedBrowseFacetRepresentation $facet): string
    {
        $templates = [];
        $templateIds = $facet->data('template_ids', []);
        foreach ($templateIds as $templateId) {
            $template = $view->api()->read('resource_templates', $templateId)->getContent();
            $templates[] = $template;
        }

        $singleSelect = null;
        if ('single_select' === $facet->data('select_type')) {
            // Prepare "Single select" select type.
            $valueOptions = [];
            foreach ($templates as $template) {
                $valueOptions[] = [
                    'value' => $template->id(),
                    'label' => $template->label(),
                    'attributes' => [],
                ];
            }
            $singleSelect = $this->formElements->get(LaminasElement\Select::class);
            $singleSelect->setName(sprintf('resource_template_%s', $facet->id()));
            $singleSelect->setValueOptions($valueOptions);
            $singleSelect->setEmptyOption('Select one…');
            $singleSelect->setAttribute('class', 'resource-template');
            $singleSelect->setAttribute('style', 'width: 90%;');
        }

        return $view->partial('common/faceted-browse/facet-render/resource-template', [
            'facet' => $facet,
            'templates' => $templates,
            'singleSelect' => $singleSelect,
        ]);
    }
}
