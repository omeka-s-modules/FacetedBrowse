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
     * @ManyToMany(
     *     targetEntity="FacetedBrowse\Entity\FacetedBrowseCategory"
     * )
     * @JoinTable(
     *     name="faceted_browse_page_category",
     *     joinColumns={
     *         @JoinColumn(
     *             name="page_id",
     *             referencedColumnName="id"
     *         )
     *     },
     *     inverseJoinColumns={
     *         @JoinColumn(
     *             name="category_id",
     *             referencedColumnName="id"
     *         )
     *     }
     * )
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

    /**
     * @PrePersist
     */
    public function prePersist(LifecycleEventArgs $eventArgs) : void
    {
        $this->setCreated(new DateTime('now'));
    }
}
