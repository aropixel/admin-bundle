<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\File\Library\DataTable;

use Aropixel\AdminBundle\Infrastructure\DataTable\Repository\Doctrine\DefaultDataTableRepository;

class DataTableRepository extends DefaultDataTableRepository
{
    protected $repositoryMethodName = 'getCategoryQueryDataTable';

}
