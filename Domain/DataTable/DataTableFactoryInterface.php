<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

use Symfony\Component\HttpFoundation\Request;

interface DataTableFactoryInterface
{
    public function create(string $className, array $columns) : DataTableInterface;
    public function getContext() : DataTableContext;
}