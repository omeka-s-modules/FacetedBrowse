<?php
namespace FacetedBrowse\Form;

use FacetedBrowse\Entity\FacetedBrowsePage;
use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Form;

class PageForm extends Form
{
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
                    'value' => FacetedBrowsePage::RESOURCE_TYPES[$page->resourceType()],
                ],
            ]);


        } else {
            $this->add([
                'type' => LaminasElement\Select::class,
                'name' => 'o-module-faceted_browse:resource_type',
                'options' => [
                    'label' => 'Resource type', // @translate
                    'info' => 'Select the type of resources to browse.', // @translate
                    'value_options' => FacetedBrowsePage::RESOURCE_TYPES,
                ],
            ]);

        }
        $this -> add([
            'type' => LaminasElement\Select::class,
            'name' => 'o-module-faceted_browse:thumbnail_type',
            'options' => [
                'label' => 'Thumbnail type',
                'info' => 'Select the thumbnail size for images on this page.',
                'value_options' => FacetedBrowsePage::THUMBNAIL_TYPES,
            ],

        ]);
    }
}
