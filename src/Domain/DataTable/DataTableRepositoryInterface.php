<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

use Aropixel\AdminBundle\Domain\DataTable\DataTableInterface;

interface DataTableRepositoryInterface
{
    public function getRowsContent(DataTableInterface $dataTable): iterable;

    public function count(DataTableInterface $dataTable): int;
}
