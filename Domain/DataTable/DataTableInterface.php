<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

use Symfony\Component\HttpFoundation\Response;

interface DataTableInterface
{
    public function getContext() : DataTableContext;

    public function getColumns() : array;

    public function getRows(DataTableRowFactoryInterface $dataTableRowFactory) : array;

    public function getResponse(DataTableRowFactoryInterface $dataTableRowFactory) : Response;

    public function getTotal() : int;

}