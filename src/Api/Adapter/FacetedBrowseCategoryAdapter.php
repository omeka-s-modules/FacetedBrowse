<?php
namespace FacetedBrowse\Api\Adapter;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use FacetedBrowse\Entity\FacetedBrowseColumn;
use FacetedBrowse\Entity\FacetedBrowseFacet;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Entity\Site;
use Omeka\Stdlib\ErrorStore;

class FacetedBrowseCategoryAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [
        'name' => 'name',
    ];

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
        if (isset($query['site_id']) && is_numeric($query['site_id'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.site',
                $this->createNamedParameter($qb, $query['site_id'])
            ));
        }
        if (isset($query['page_id']) && is_numeric($query['page_id'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.page',
                $this->createNamedParameter($qb, $query['page_id'])
            ));
        }
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

            $pageData = $request->getValue('o-module-faceted_browse:page');
            $page = $this->getAdapter('faceted_browse_pages')->findEntity($pageData['o:id']);
            $entity->setPage($page);

            $lastCategory = $page->getCategories()->last();
            $entity->setPosition($lastCategory ? $lastCategory->getPosition() + 1 : 1);
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
        if ($this->shouldHydrate($request, 'o-module-faceted_browse:sort_by')) {
            $entity->setSortBy($request->getValue('o-module-faceted_browse:sort_by'));
        }
        if ($this->shouldHydrate($request, 'o-module-faceted_browse:sort_order')) {
            $entity->setSortOrder($request->getValue('o-module-faceted_browse:sort_order'));
        }
        if ($this->shouldHydrate($request, 'o-module-faceted_browse:helper_text')) {
            $entity->setHelperText($request->getValue('o-module-faceted_browse:helper_text'));
        }
        if ($this->shouldHydrate($request, 'o-module-faceted_browse:helper_text_button_label')) {
            $entity->setHelperTextButtonLabel($request->getValue('o-module-faceted_browse:helper_text_button_label'));
        }
        if ($this->shouldHydrate($request, 'o-module-faceted_browse:facet')) {
            $facets = $request->getValue('o-module-faceted_browse:facet');
            if (is_array($facets)) {
                $facetCollection = $entity->getFacets();
                $toRetain = [];
                $position = 1;
                foreach ($facets as $facet) {
                    if (isset($facet['o:id']) && $facetCollection->containsKey($facet['o:id'])) {
                        // This is an existing facet.
                        $facetEntity = $facetCollection->get($facet['o:id']);
                    } else {
                        // This is a new facet.
                        $facetEntity = new FacetedBrowseFacet;
                        $facetEntity->setCategory($entity);
                        $facetCollection->add($facetEntity);
                    }
                    $facetEntity->setType($facet['o-module-faceted_browse:type']);
                    $facetEntity->setName($facet['o:name']);
                    $data = is_string($facet['o:data']) ? json_decode($facet['o:data'], true) : $facet['o:data'];
                    $facetEntity->setData($data);
                    $facetEntity->setPosition($position++);
                    $toRetain[] = $facetEntity;
                }
                // Remove any facet entities that are unused.
                foreach ($facetCollection as $index => $facetEntity) {
                    if (!in_array($facetEntity, $toRetain)) {
                        $facetCollection->remove($index);
                    }
                }
            }
        }
        if ($this->shouldHydrate($request, 'o-module-faceted_browse:column')) {
            $columns = $request->getValue('o-module-faceted_browse:column');
            if (is_array($columns)) {
                $columnCollection = $entity->getColumns();
                $toRetain = [];
                $position = 1;
                foreach ($columns as $column) {
                    if (isset($column['o:id']) && $columnCollection->containsKey($column['o:id'])) {
                        // This is an existing column.
                        $columnEntity = $columnCollection->get($column['o:id']);
                    } else {
                        // This is a new column.
                        $columnEntity = new FacetedBrowseColumn;
                        $columnEntity->setCategory($entity);
                        $columnCollection->add($columnEntity);
                    }
                    $columnEntity->setType($column['o-module-faceted_browse:type']);
                    $columnEntity->setName($column['o:name']);
                    $columnEntity->setExcludeSortBy($column['o-module-faceted_browse:exclude_sort_by']);
                    $data = is_string($column['o:data']) ? json_decode($column['o:data'], true) : $column['o:data'];
                    $columnEntity->setData($data);
                    $columnEntity->setPosition($position++);
                    $toRetain[] = $columnEntity;
                }
                // Remove any column entities that are unused.
                foreach ($columnCollection as $index => $columnEntity) {
                    if (!in_array($columnEntity, $toRetain)) {
                        $columnCollection->remove($index);
                    }
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
