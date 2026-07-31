<?php
namespace FacetedBrowse\FacetType;

use Doctrine\ORM\QueryBuilder;

/**
 * Show-all behaviour shared by this module's facet types.
 */
trait ShowAllTrait
{
    /**
     * A count sort falls back to the label columns ascending, a label sort uses
     * them alone.
     *
     * @param array $labelColumns What orders the label, in precedence order: the
     * label alias, or its components for a composite label, since ordering by a
     * concatenation sorts by whatever comes first in it.
     */
    protected function applyShowAllSort(QueryBuilder $qb, array $options, array $labelColumns): void
    {
        if ('has_count' === $options['sort_by']) {
            $qb->addOrderBy('has_count', $options['sort_order']);
            foreach ($labelColumns as $column) {
                $qb->addOrderBy($column, 'asc');
            }
            return;
        }
        foreach ($labelColumns as $column) {
            $qb->addOrderBy($column, $options['sort_order']);
        }
    }
}
