<?php

namespace Aropixel\AdminBundle\Infrastructure\DataTable\Repository\Doctrine;

use Aropixel\AdminBundle\Domain\DataTable\DataTableInterface;
use Aropixel\AdminBundle\Domain\DataTable\DataTableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DefaultDataTableRepository implements DataTableRepositoryInterface
{
    protected $repositoryMethodName = 'getQueryDataTable';

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function getRowsContent(DataTableInterface $dataTable): iterable
    {
        $context = $dataTable->getContext();
        $qb = $this->em->getRepository($dataTable->getClassName())->{$this->repositoryMethodName}($context);

        $query = $qb->getQuery();
        $query->setFirstResult($context->getStart() ?: 0);
        $query->setMaxResults($context->getLength() ?: 50);

        return $query->getResult();
    }

    public function count(DataTableInterface $dataTable): int
    {
        $context = $dataTable->getContext();

        $qb = $this->em->getRepository($dataTable->getClassName())->{$this->repositoryMethodName}($context);

        $qb->select('COUNT(' . $this->getTableAs($dataTable) . ')');

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    protected function getTableAs(DataTableInterface $dataTable): string
    {
        $reflection = new \ReflectionClass($dataTable->getClassName());
        $shortName = $reflection->getShortName();

        return mb_strtolower(mb_substr($shortName, 0, 1));
    }
}
