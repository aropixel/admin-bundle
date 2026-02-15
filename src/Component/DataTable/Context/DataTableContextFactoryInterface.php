<?php

namespace Aropixel\AdminBundle\Component\DataTable\Context;

interface DataTableContextFactoryInterface
{
    public function create(): DataTableContext;
}
