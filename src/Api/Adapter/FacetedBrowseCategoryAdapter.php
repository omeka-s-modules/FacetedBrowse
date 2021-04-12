<?php
namespace FacetedBrowse\Api\Adapter;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use FacetedBrowse\Entity\FacetedBrowseFacet;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Entity\Site;
use Omeka\Stdlib\ErrorStore;

class FacetedBrowseCategoryAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [];

    public function getResourceName()
    {
        return 'faceted_browse_categories';
    }

    public function getRepresentationClass()
    {
        return 'FacetedBrowse\Api\Representation\FacetedBrowseCategoryRepresentation';
    }

    public function getEntityClass()
    {
        return 'FacetedBrowse\Entity\FacetedBrowseCategory';
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
    }

    public function validateRequest(Request $request, ErrorStore $errorStore)
    {
        $data = $request->getContent();
        if (Request::CREATE === $request->getOperation()) {
            // These values are unalterable after creation.
            if (!isset($data['o:site']['o:id'])) {
                $errorStore->addError('o:site', 'A site must have an ID'); // @translate
            }
        }
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        if (Request::CREATE === $request->getOperation()) {
            $siteData = $request->getValue('o:site');
            $site = $this->getAdapter('sites')->findEntity($siteData['o:id']);
            $entity->setSite($site);
        }
        if (Request::UPDATE === $request->getOperation()) {
            $entity->setModified(new DateTime('now'));
        }
        $this->hydrateOwner($request, $entity);
        if ($this->shouldHydrate($request, 'o:name')) {
            $entity->setName($request->getValue('o:name'));
        }
        if ($this->shouldHydrate($request, 'o:query')) {
            $entity->setQuery($request->getValue('o:query'));
        }
        if ($this->shouldHydrate($request, 'o-module-faceted_browse:facet')) {
            $facets = $request->getValue('o-module-faceted_browse:facet');
            if (is_array($facets)) {
                $facetCollection = $entity->getFacets();
                $facetCollection->clear();
                $position = 1;
                foreach ($facets as $facet) {
                    $facetEntity = new FacetedBrowseFacet;
                    $facetEntity->setCategory($entity);
                    $facetEntity->setType($facet['o-module-faceted_browse:type']);
                    $facetEntity->setName($facet['o:name']);
                    $facetEntity->setData(json_decode($facet['o:data'], true));
                    $facetEntity->setPosition($position++);
                    $facetCollection->add($facetEntity);
                }
            }
        }
    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        if (!($entity->getSite() instanceof Site)) {
            $errorStore->addError('o:site', 'A category must have a site'); // @translate
        }
        if (!is_string($entity->getName()) || '' === $entity->getName()) {
            $errorStore->addError('o:name', 'A category must have a name'); // @translate
        }
        if (!is_string($entity->getQuery())) {
            $errorStore->addError('o:query', 'A category must have a query'); // @translate
        }
    }
}
