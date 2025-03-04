<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

interface DataTableRepositoryInterface
{
    /**
     * @return mixed[]
     */
    public function getRowsContent(DataTableInterface $dataTable): iterable;

    public function count(DataTableInterface $dataTable): int;
}
