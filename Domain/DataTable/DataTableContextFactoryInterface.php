<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

interface DataTableContextFactoryInterface
{
    public function create(): DataTableContext;
}
