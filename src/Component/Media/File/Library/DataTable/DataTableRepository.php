<?php

namespace Aropixel\AdminBundle\Component\Media\File\Library\DataTable;

use Aropixel\AdminBundle\Component\DataTable\Repository\DataTableRepository;

class DataTableRepository extends DataTableRepository
{
    protected string $repositoryMethodName = 'getCategoryQueryDataTable';
}
