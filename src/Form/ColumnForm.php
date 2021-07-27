<?php
namespace FacetedBrowse\Form;

use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Form;

class ColumnForm extends Form
{
    public function init()
    {
        $this->add([
            'type' => LaminasElement\Hidden::class,
            'name' => 'column_type',
            'attributes' => [
                'id' => 'column-type-input',
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Text::class,
            'name' => 'column_name',
            'options' => [
                'label' => 'Column name', // @translate
            ],
            'attributes' => [
                'id' => 'column-name-input',
                'required' => true,
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Button::class,
            'name' => 'column_set',
            'options' => [
                'label' => 'Set column', // @translate
            ],
            'attributes' => [
                'id' => 'column-set-button',
                'style' => 'width: 100%;',
            ],
        ]);
    }
}
