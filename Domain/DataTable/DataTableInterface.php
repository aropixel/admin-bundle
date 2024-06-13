<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

use Aropixel\AdminBundle\Domain\DataTable\DataTableContext;
use Aropixel\AdminBundle\Domain\DataTable\DataTableRowFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

interface DataTableInterface
{
    public function getClassName() : string;

    public function getContext() : DataTableContext;

    public function getColumns() : array;

    public function getRowsContent() : iterable;

    public function getRows(DataTableRowFactoryInterface $dataTableRowFactory) : array;

    public function getResponse(DataTableRowFactoryInterface $dataTableRowFactory) : Response;

    public function getTotal() : int;

}
