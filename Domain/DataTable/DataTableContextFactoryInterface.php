<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 20/02/2023 à 15:22
 */

namespace Aropixel\AdminBundle\Domain\DataTable;

interface DataTableContextFactoryInterface
{
    public function create() : DataTableContext;
}
