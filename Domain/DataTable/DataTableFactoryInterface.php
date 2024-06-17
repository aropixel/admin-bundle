<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

interface DataTableFactoryInterface
{
    public function setRepository(DataTableRepositoryInterface $dataTableRepository): self;

    public function create(string $className, array $columns): DataTableInterface;
}
