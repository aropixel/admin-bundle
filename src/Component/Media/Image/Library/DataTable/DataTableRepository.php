<?php

namespace Aropixel\AdminBundle\Component\Media\Image\Library\DataTable;

use Aropixel\AdminBundle\Component\DataTable\Repository\DefaultDataTableRepository;

class DataTableRepository extends DefaultDataTableRepository
{
    protected string $repositoryMethodName = 'getCategoryQueryDataTable';
}
