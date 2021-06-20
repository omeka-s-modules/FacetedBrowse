<?php
namespace FacetedBrowse\FacetType;

use FacetedBrowse\Api\Representation\FacetedBrowsePageRepresentation;
use Omeka\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

class Manager extends AbstractPluginManager
{
    protected $autoAddInvokableClass = false;

    protected $instanceOf = FacetTypeInterface::class;

    public function get($name, $options = [], $usePeeringServiceManagers = true)
    {
        try {
            $instance = parent::get($name, $options, $usePeeringServiceManagers);
        } catch (ServiceNotFoundException $e) {
            $instance = new Unknown($name, $this->creationContext->get('FormElementManager'));
        }
        return $instance;
    }

    /**
     * Get facet name=>label value options for use in a select element.
     *
     * @param FacetedBrowsePageRepresentation $page
     * @return array
     */
    public function getValueOptions(FacetedBrowsePageRepresentation $page)
    {
        $valueOptions = [];
        foreach ($this->getRegisteredNames() as $facetTypeName) {
            $facetType = $this->get($facetTypeName);
            if (in_array($page->resourceType(), $facetType->getResourceTypes())) {
                $valueOptions[] = [
                    'value' => $facetTypeName,
                    'label' => $facetType->getLabel(),
                    'attributes' => [
                        'data-max-facets' => $facetType->getMaxFacets(),
                    ],
                ];
            }
        }
        return $valueOptions;
    }
}
