<?php

namespace Aropixel\AdminBundle\Component\DataTable;

use Aropixel\AdminBundle\Component\DataTable\Column\DataTableColumn;
use Aropixel\AdminBundle\Component\DataTable\Repository\DataTableRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

interface DataTableFactoryInterface
{
    public function setRepository(DataTableRepositoryInterface $dataTableRepository): self;

    /**
     * @param DataTableColumn[] $columns
     * @return DataTableInterface&Response
     */
    public function create(string $className, array $columns = [], string $mode = DataTableInterface::MODE_XHR): DataTableInterface;
}
