<?php

namespace Aropixel\AdminBundle\Component\DataTable;

use Aropixel\AdminBundle\Component\DataTable\Column\DataTableColumn;
use Aropixel\AdminBundle\Component\DataTable\Repository\DataTableRepositoryInterface;

interface DataTableFactoryInterface
{
    public function setRepository(DataTableRepositoryInterface $dataTableRepository): self;

    /**
     * @param DataTableColumn[] $columns
     */
    public function create(string $className, array $columns): DataTableInterface;
}
