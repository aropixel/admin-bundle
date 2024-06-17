<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

interface DataTableRowFactoryInterface
{
    public function createRow($subject): array;
}
