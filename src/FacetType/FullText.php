<?php
namespace FacetedBrowse\FacetType;

use FacetedBrowse\Api\Representation\FacetedBrowseFacetRepresentation;
use Laminas\Form\Element as LaminasElement;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;

class FullText implements FacetTypeInterface
{
    protected $formElements;

    public function __construct(ServiceLocatorInterface $formElements)
    {
        $this->formElements = $formElements;
    }

    public function getLabel() : string
    {
        return 'Full-text'; // @translate
    }

    public function getMaxFacets() : ?int
    {
        return 1;
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-data-form/full-text.js', 'FacetedBrowse'));
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
        return '';
    }

    public function prepareFacet(PhpRenderer $view) : void
    {
        $view->headScript()->appendFile($view->assetUrl('js/facet-render/full-text.js', 'FacetedBrowse'));
    }

    public function renderFacet(PhpRenderer $view, FacetedBrowseFacetRepresentation $facet) : string
    {
        $fullText = new LaminasElement\Text('full_text');
        $fullText->setAttribute('class', 'full-text');
        return $view->formText($fullText);
    }
}
