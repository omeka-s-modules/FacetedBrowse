<?php
namespace FacetedBrowse\FacetType;

use Laminas\Form\Element as LaminasElement;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;

class Unknown implements FacetTypeInterface
{
    protected $name;
    protected $formElements;

    public function __construct(string $name, ServiceLocatorInterface $formElements)
    {
        $this->name = $name;
        $this->formElements = $formElements;
    }

    public function getLabel() : string
    {
        return '[Unknown]'; // @translate
    }

    public function prepareDataForm(PhpRenderer $view) : void
    {
    }

    public function renderDataForm(PhpRenderer $view, array $data) : string
    {
        $typeElement = $this->formElements->get(LaminasElement\Text::class);
        $typeElement->setName('facet_type_unknown');
        $typeElement->setOptions([
            'label' => 'Unknown facet type', // @translate
        ]);
        $typeElement->setAttributes([
            'value' => $this->name,
            'disabled' => true,
        ]);

        $dataElement = $this->formElements->get(LaminasElement\Textarea::class);
        $dataElement->setName('facet_data_unknown');
        $dataElement->setOptions([
            'label' => 'Unknown facet data', // @translate
        ]);
        $dataElement->setAttributes([
            'value' => json_encode($data, JSON_PRETTY_PRINT),
            'style' => 'height: 300px;',
            'disabled' => true,
        ]);

        return sprintf(
            '%s%s',
            $view->formRow($typeElement),
            $view->formRow($dataElement),
        );
    }

    public function renderFacet(PhpRenderer $view, $facet) : string
    {
        return '';
    }
}
