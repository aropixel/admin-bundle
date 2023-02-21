<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 21/02/2023 à 13:45
 */

namespace Aropixel\AdminBundle\Domain\Media\Image\Library\Repository;

use Aropixel\AdminBundle\Domain\DataTable\DataTableContext;

interface ImageRepositoryInterface
{
    public function getQueryDataTable(DataTableContext $context);
    public function getCategoryQueryDataTable(DataTableContext $context);

}
