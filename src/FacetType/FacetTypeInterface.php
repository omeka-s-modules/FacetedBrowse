<?php
namespace FacetedBrowse\FacetType;

use Laminas\Form\Fieldset;
use Laminas\View\Renderer\PhpRenderer;

interface FacetTypeInterface
{
    /**
     * Get the label of this facet type.
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Prepare the data form of this facet type.
     *
     * @param PhpRenderer $view
     */
    public function prepareDataForm(PhpRenderer $view) : void;

    /**
     * Set the data elements to the fieldset for this facet type.
     *
     * @param Fieldset $fieldset
     */
    public function setDataElements(Fieldset $fieldset, array $data) : void;
}
