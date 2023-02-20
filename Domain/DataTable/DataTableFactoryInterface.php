<?php

namespace Aropixel\AdminBundle\Domain\DataTable;


interface DataTableFactoryInterface
{
    public function setRepository(DataTableRepositoryInterface $dataTableRepository);

    public function create(string $className, array $columns) : DataTableInterface;

}