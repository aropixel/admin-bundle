<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

use Aropixel\AdminBundle\Entity\ItemLibraryInterface;

interface DataTableRowFactoryInterface
{
    /**
     * @return array<string>
     */
    public function createRow(ItemLibraryInterface $subject): array;
}
