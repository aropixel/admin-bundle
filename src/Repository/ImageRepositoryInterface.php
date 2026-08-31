<?php

namespace Aropixel\AdminBundle\Repository;

use Aropixel\AdminBundle\Component\DataTable\Context\DataTableContext;
use Doctrine\ORM\QueryBuilder;

interface ImageRepositoryInterface
{
    /**
     * Sans type de retour natif : ORM 2.20 ne le déclare pas sur EntityRepository::find(),
     * ORM 3 le déclare (covariant). L'interface doit rester implémentable par les deux.
     *
     * @return object|null
     */
    public function find(mixed $id);

    public function getQueryDataTable(DataTableContext $context): QueryBuilder;

    public function getCategoryQueryDataTable(DataTableContext $context): QueryBuilder;
}
