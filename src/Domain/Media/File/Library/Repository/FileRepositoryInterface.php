<?php

namespace Aropixel\AdminBundle\Domain\Media\File\Library\Repository;

use Aropixel\AdminBundle\Domain\DataTable\DataTableContext;
use Doctrine\ORM\QueryBuilder;

interface FileRepositoryInterface
{
    public function find(mixed $id): object|null;

    public function getQueryDataTable(DataTableContext $context): QueryBuilder;

    public function getCategoryQueryDataTable(DataTableContext $context): QueryBuilder;
}
