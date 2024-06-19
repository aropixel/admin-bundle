<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

use Aropixel\AdminBundle\Domain\DataTable\DataTableContext;

interface DataTableContextFactoryInterface
{
    public function create(): DataTableContext;
}
