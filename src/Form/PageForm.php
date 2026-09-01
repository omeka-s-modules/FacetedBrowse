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
                'id' => 'page-title',
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
                    'id' => 'page-resource-type',
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
                'attributes' => [
                    'id' => 'page-resource-type',
                ],
            ]);
        }
        $this->add([
            'type' => LaminasElement\Select::class,
            'name' => 'o-module-faceted_browse:thumbnail_type',
            'options' => [
                'label' => 'Thumbnail type', // @translate
                'info' => 'Select the type of thumbnail images to render on the page.', // @translate
                'empty_option' => 'Default', // @translate
                'value_options' => FacetedBrowsePage::THUMBNAIL_TYPES,
            ],
            'attributes' => [
                'id' => 'page-thumbnail-type',
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'o-module-faceted_browse:thumbnail_type',
            'allow_empty' => true,
        ]);
    }
}
