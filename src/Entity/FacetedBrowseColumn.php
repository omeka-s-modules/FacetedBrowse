<?php
namespace FacetedBrowse\Entity;

use Omeka\Entity\AbstractEntity;

/**
 * @Entity
 */
class FacetedBrowseColumn extends AbstractEntity
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
     *     targetEntity="FacetedBrowseCategory",
     *     inversedBy="columns"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $category;

    /**
     * @Column(
     *     type="string",
     *     length=255,
     *     nullable=false
     * )
     */
    protected $name;

    /**
     * @Column(
     *     type="string",
     *     length=255,
     *     nullable=false
     * )
     */
    protected $type;

    /**
     * @Column(
     *     type="boolean",
     *     nullable=false
     * )
     */
    protected $excludeSortBy;

    /**
     * @Column(
     *     type="json",
     *     nullable=false
     * )
     */
    protected $data;

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

    public function setCategory(FacetedBrowseCategory $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): FacetedBrowseCategory
    {
        return $this->category;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setExcludeSortBy(bool $excludeSortBy): void
    {
        $this->excludeSortBy = $excludeSortBy;
    }

    public function getExcludeSortBy(): bool
    {
        return $this->excludeSortBy;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
}
