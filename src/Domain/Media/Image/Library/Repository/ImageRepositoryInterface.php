<?php

namespace Aropixel\AdminBundle\Domain\Media\Image\Library\Repository;

use Aropixel\AdminBundle\Domain\DataTable\DataTableContext;

interface ImageRepositoryInterface
{
    public function getQueryDataTable(DataTableContext $context);

    public function getCategoryQueryDataTable(DataTableContext $context);
}
