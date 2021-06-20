<?php
namespace FacetedBrowse\Form;

use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Form;

class PageForm extends Form
{
    const RESOURCE_TYPES = [
        'items' => 'Items', // @translate
        'item_sets' => 'Item sets', // @translate
        'media' => 'Media', // @translate
    ];

    public function init()
    {
        $page = $this->getOption('page');

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
        if ($page) {
            $this->add([
                'type' => LaminasElement\Text::class,
                'name' => 'resource_type',
                'options' => [
                    'label' => 'Resource type', // @translate
                ],
                'attributes' => [
                    'disabled' => true,
                    'value' => self::RESOURCE_TYPES[$page->resourceType()],
                ],
            ]);
        } else {
            $this->add([
                'type' => LaminasElement\Select::class,
                'name' => 'o-module-faceted_browse:resource_type',
                'options' => [
                    'label' => 'Resource type', // @translate
                    'info' => 'Select the type of resources to browse.', // @translate
                    'value_options' => self::RESOURCE_TYPES,
                ],
            ]);
        }
    }
}
