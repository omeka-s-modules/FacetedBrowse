<?php
namespace FacetedBrowse\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Omeka\Entity\AbstractEntity;

/**
 * @Entity
 * @Table(
 *     uniqueConstraints={
 *         @UniqueConstraint(
 *             columns={"page_id", "category_id"}
 *         )
 *     }
 * )
 */
class FacetedBrowsePageCategory extends AbstractEntity
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
     *     targetEntity="FacetedBrowse\Entity\FacetedBrowsePage",
     *     inversedBy="pageCategories"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $page;

    /**
     * @ManyToOne(
     *     targetEntity="FacetedBrowse\Entity\FacetedBrowseCategory",
     *     inversedBy="pageCategories"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $category;

    /**
     * @Column(
     *     type="integer",
     *     nullable=false
     * )
     */
    protected $position;

    public function getId()
    {
        return $this->id;
    }

    public function setPage(FacetedBrowsePage $page) : void
    {
        $this->page = $page;
    }

    public function getPage() : FacetedBrowsePage
    {
        return $this->page;
    }

    public function setCategory(FacetedBrowseCategory $category) : void
    {
        $this->category = $category;
    }

    public function getCategory() : FacetedBrowseCategory
    {
        return $this->category;
    }

    public function setPosition(int $position) : void
    {
        $this->position = $position;
    }

    public function getPosition() : int
    {
        return $this->position;
    }
}
