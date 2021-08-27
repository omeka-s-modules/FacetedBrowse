<?php
namespace FacetedBrowse\Api\Adapter;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Entity\Site;
use Omeka\Stdlib\ErrorStore;

class FacetedBrowsePageAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [
        'title' => 'title',
    ];

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
        if (isset($query['site_id']) && is_numeric($query['site_id'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.site',
                $this->createNamedParameter($qb, $query['site_id'])
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
        $em = $this->getServiceLocator()->get('Omeka\EntityManager');
        if (Request::CREATE === $request->getOperation()) {
            $siteData = $request->getValue('o:site');
            $site = $this->getAdapter('sites')->findEntity($siteData['o:id']);
            $entity->setSite($site);

            $resourceType = $request->getValue('o-module-faceted_browse:resource_type');
            $resourceType = in_array($resourceType, ['items', 'item_sets', 'media']) ? $resourceType : 'items';
            $entity->setResourceType($resourceType);
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
                $position = 1;
                foreach ($categories as $category) {
                    $cEntity = $em->find('FacetedBrowse\Entity\FacetedBrowseCategory', $category['o:id']);
                    if ($cEntity) {
                        $cEntity->setPosition($position++);
                    }
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
