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

class FacetedBrowsePageAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [];

    public function getResourceName()
    {
        return 'faceted_browse_pages';
    }

    public function getRepresentationClass()
    {
        return 'FacetedBrowse\Api\Representation\FacetedBrowsePageRepresentation';
    }

    public function getEntityClass()
    {
        return 'FacetedBrowse\Entity\FacetedBrowsePage';
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
        $em = $this->getServiceLocator()->get('Omeka\EntityManager');
        if (Request::CREATE === $request->getOperation()) {
            $siteData = $request->getValue('o:site');
            $site = $this->getAdapter('sites')->findEntity($siteData['o:id']);
            $entity->setSite($site);
        }
        if (Request::UPDATE === $request->getOperation()) {
            $entity->setModified(new DateTime('now'));
        }
        $this->hydrateOwner($request, $entity);
        if ($this->shouldHydrate($request, 'o:title')) {
            $entity->setTitle($request->getValue('o:title'));
        }
        if ($this->shouldHydrate($request, 'o-module-faceted_browse:category')) {
            $categories = $request->getValue('o-module-faceted_browse:category');
            if (is_array($categories)) {
                $collection = $entity->getPageCategories();
                $toRetain = [];
                $toAdd = [];
                $position = 1;
                foreach ($categories as $category) {
                    // Get category entity.
                    $cEntity = $em->find('FacetedBrowse\Entity\FacetedBrowseCategory', $category['o:id']);
                    if ($cEntity) {
                        $pcEntity = $collection->current();
                        if ($pcEntity) {
                            // Reuse an existing page/category entity.
                            $collection->next();
                            $toRetain[] = $pcEntity;
                        } else {
                            // Create a new page/category entity.
                            $pcEntity = new FacetedBrowsePageCategory;
                            $pcEntity->setPage($entity);
                            $toAdd[] = $pcEntity;
                        }
                        // @todo: get category entity and set it here.
                        $pcEntity->setCategory($cEntity);
                        $pcEntity->setPosition($position++);
                    }
                }
                // Remove any existing page/category entities that are unused.
                foreach ($collection as $index => $pcEntity) {
                    if (!in_array($pcEntity, $toRetain)) {
                        $collection->remove($index);
                    }
                }
                // Add any new page/category entities.
                foreach ($toAdd as $pcEntity) {
                    $collection->add($pcEntity);
                }
            }
        }

    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        if (!($entity->getSite() instanceof Site)) {
            $errorStore->addError('o:site', 'A page must have a site'); // @translate
        }
        if (!is_string($entity->getTitle()) || '' === $entity->getTitle()) {
            $errorStore->addError('o:name', 'A page must have a title'); // @translate
        }
    }
}
