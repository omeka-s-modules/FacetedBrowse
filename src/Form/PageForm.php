<?php
namespace FacetedBrowse\Form;

use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Form;
use Omeka\Form\Element as OmekaElement;

class PageForm extends Form
{
    public function init()
    {
        $categories = $this->getOption('categories');
        $valueOptions = [];
        foreach ($categories as $category) {
            $valueOptions[$category->id()] = $category->name();
        }

        $this->add([
            'type' => LaminasElement\Text::class,
            'name' => 'o:title',
            'options' => [
                'label' => 'Title', // @translate
                'info' => 'Enter the title of this page.', // @translate
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
        $this->add([
            'type' => LaminasElement\Select::class,
            'name' => 'category',
            'options' => [
                'label' => 'Category', // @translate
                'empty_option' => 'Add a category', // @translate
                'value_options' => $valueOptions,
            ],
            'attributes' => [
                'id' => 'category-select',
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'category',
            'allow_empty' => true,
        ]);
    }
}
