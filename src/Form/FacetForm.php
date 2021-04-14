<?php
namespace FacetedBrowse\Form;

use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Form;

class FacetForm extends Form
{
    public function init()
    {
        $this->add([
            'type' => LaminasElement\Hidden::class,
            'name' => 'facet_type',
            'attributes' => [
                'id' => 'facet-type-input',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Text::class,
            'name' => 'facet_name',
            'options' => [
                'label' => 'Facet name', // @translate
            ],
            'attributes' => [
                'id' => 'facet-name-input',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Button::class,
            'name' => 'facet_set',
            'options' => [
                'label' => 'Set facet',
            ],
            'attributes' => [
                'id' => 'facet-set-button',
                'style' => 'width: 100%;',
            ],
        ]);
    }
}
