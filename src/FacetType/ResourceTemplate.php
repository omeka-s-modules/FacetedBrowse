<?php
namespace FacetedBrowse\FacetType;

use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
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

    public function getLabel() : string
    {
        return 'Resource template'; // @translate
    }

    public function getMaxFacets() : ?int
    {
        return null;
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-data-form/resource-template.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
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
            'elementTemplateIds' => $templateIds,
        ]);
    }

    public function prepareFacet(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-render/resource-template.js', 'FacetedBrowse'));
    }

    public function renderFacet(PhpRenderer $view, FacetedBrowseFacetRepresentation $facet) : string
    {
        $templates = [];
        $templateIds = $facet->data('template_ids', []);
        foreach ($templateIds as $templateId) {
            $template = $view->api()->read('resource_templates', $templateId)->getContent();
            $templates[] = $template;
        }
        return $view->partial('common/faceted-browse/facet-render/resource-template', [
            'facet' => $facet,
            'templates' => $templates,
        ]);
    }
}
