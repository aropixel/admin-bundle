<?php

namespace Aropixel\AdminBundle\Repository;

use Aropixel\AdminBundle\Component\DataTable\Context\DataTableContext;
use Doctrine\ORM\QueryBuilder;

interface FileRepositoryInterface
{
    public function find(mixed $id): object|null;

    public function getQueryDataTable(DataTableContext $context): QueryBuilder;

    public function getCategoryQueryDataTable(DataTableContext $context): QueryBuilder;
}
