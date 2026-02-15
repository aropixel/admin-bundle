<?php

namespace Aropixel\AdminBundle\Component\DataTable\Repository;

use Aropixel\AdminBundle\Component\DataTable\DataTableInterface;

interface DataTableRepositoryInterface
{
    /**
     * @return mixed[]
     */
    public function getRowsContent(DataTableInterface $dataTable): iterable;

    public function count(DataTableInterface $dataTable): int;
}
