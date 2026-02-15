<?php

namespace Aropixel\AdminBundle\Component\Select2;

use Doctrine\ORM\QueryBuilder;

interface Select2RepositoryInterface
{
    public function getQuerySelect2(string $query): QueryBuilder;
}