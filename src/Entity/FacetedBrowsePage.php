<?php
namespace FacetedBrowse\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Site;
use Omeka\Entity\User;

/**
 * @Entity
 * @HasLifecycleCallbacks
 */
class FacetedBrowsePage extends AbstractEntity
{
    const RESOURCE_TYPES = [
        'items' => 'Items', // @translate
        'item_sets' => 'Item sets', // @translate
        'media' => 'Media', // @translate
    ];

    /**
     * @Id
     * @Column(
     *     type="integer",
     *     options={
     *         "unsigned"=true
     *     }
     * )
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ManyToOne(
     *     targetEntity="Omeka\Entity\User"
     * )
     * @JoinColumn(
     *     nullable=true,
     *     onDelete="SET NULL"
     * )
     */
    protected $owner;

    /**
     * @ManyToOne(
     *     targetEntity="Omeka\Entity\Site"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $site;

    /**
     * @Column(
     *     type="datetime",
     *     nullable=false
     * )
     */
    protected $created;

    /**
     * @Column(
     *     type="datetime",
     *     nullable=true
     * )
     */
    protected $modified;

    /**
     * @Column(
     *     type="string",
     *     length=255,
     *     nullable=false
     * )
     */
    protected $title;

    /**
     * @Column(
     *     type="string",
     *     length=255,
     *     nullable=false
     * )
     */
    protected $resourceType;

    /**
     * @OneToMany(
     *     targetEntity="FacetedBrowse\Entity\FacetedBrowseCategory",
     *     mappedBy="page",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove", "detach"}
     * )
     * @OrderBy({"position" = "ASC"})
     */
    protected $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setOwner(?User $owner = null) : void
    {
        $this->owner = $owner;
    }

    public function getOwner() : ?User
    {
        return $this->owner;
    }

    public function setSite(Site $site) : void
    {
        $this->site = $site;
    }

    public function getSite() : Site
    {
        return $this->site;
    }

    public function setCreated(DateTime $created) : void
    {
        $this->created = $created;
    }

    public function getCreated() : DateTime
    {
        return $this->created;
    }

    public function setModified(DateTime $modified) : void
    {
        $this->modified = $modified;
    }

    public function getModified() : ?DateTime
    {
        return $this->modified;
    }

    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setResourceType(string $resourceType) : void
    {
        $this->resourceType = $resourceType;
    }

    public function getResourceType() : string
    {
        return $this->resourceType;
    }

    public function getCategories() : Collection
    {
        return $this->categories;
    }

    /**
     * @PrePersist
     */
    public function prePersist(LifecycleEventArgs $eventArgs) : void
    {
        $this->setCreated(new DateTime('now'));
    }
}
