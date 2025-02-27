<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

use Aropixel\AdminBundle\Entity\ItemLibraryInterface;

interface DataTableRowFactoryInterface
{
    public function createRow(ItemLibraryInterface $subject): array;
}
