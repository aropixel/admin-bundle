<?php

namespace Aropixel\AdminBundle\Repository;

use Aropixel\AdminBundle\Component\DataTable\Context\DataTableContext;
use Doctrine\ORM\QueryBuilder;

interface ImageRepositoryInterface
{
    public function find(mixed $id): ?object;

    public function getQueryDataTable(DataTableContext $context): QueryBuilder;

    public function getCategoryQueryDataTable(DataTableContext $context): QueryBuilder;
}
