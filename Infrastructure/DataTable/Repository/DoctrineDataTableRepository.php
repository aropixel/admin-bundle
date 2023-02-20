<?php

namespace Aropixel\AdminBundle\Infrastructure\DataTable\Repository;

use Aropixel\AdminBundle\Domain\DataTable\DataTableContext;
use Aropixel\AdminBundle\Domain\DataTable\DataTableInterface;
use Aropixel\AdminBundle\Domain\DataTable\DataTableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineDataTableRepository implements DataTableRepositoryInterface
{
    private EntityManagerInterface $em;

    public function getRowsContent(DataTableInterface $dataTable) : iterable
    {
        $context = $dataTable->getContext();
        return $this->em->getRepository($dataTable->getClassName())->getQueryDataTable($context);
    }

}