<?php

namespace Aropixel\AdminBundle\Component\DataTable\Row;

use Aropixel\AdminBundle\Entity\ItemLibraryInterface;

interface DataTableRowFactoryInterface
{
    /**
     * @return array<string>
     */
    public function createRow(ItemLibraryInterface $subject): array;
}
