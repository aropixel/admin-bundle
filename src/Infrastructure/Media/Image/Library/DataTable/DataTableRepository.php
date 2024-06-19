<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Library\DataTable;

use Aropixel\AdminBundle\Infrastructure\DataTable\Repository\Doctrine\DefaultDataTableRepository;

class DataTableRepository extends DefaultDataTableRepository
{
    protected $repositoryMethodName = 'getCategoryQueryDataTable';
}
