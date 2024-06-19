<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

use Aropixel\AdminBundle\Domain\DataTable\DataTableInterface;
use Aropixel\AdminBundle\Domain\DataTable\DataTableRepositoryInterface;

interface DataTableFactoryInterface
{
    public function setRepository(DataTableRepositoryInterface $dataTableRepository): self;

    public function create(string $className, array $columns): DataTableInterface;
}
