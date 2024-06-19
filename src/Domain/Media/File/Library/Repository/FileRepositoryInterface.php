<?php

namespace Aropixel\AdminBundle\Domain\Media\File\Library\Repository;

use Aropixel\AdminBundle\Domain\DataTable\DataTableContext;

interface FileRepositoryInterface
{
    public function getQueryDataTable(DataTableContext $context);

    public function getCategoryQueryDataTable(DataTableContext $context);
}
