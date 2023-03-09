<?php

namespace Aropixel\AdminBundle\Infrastructure\DataTable\Repository\Doctrine;

use Aropixel\AdminBundle\Domain\DataTable\DataTableInterface;
use Aropixel\AdminBundle\Domain\DataTable\DataTableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;



class DefaultDataTableRepository implements DataTableRepositoryInterface
{

    private EntityManagerInterface $em;

    protected $repositoryMethodName = 'getQueryDataTable';

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function getRowsContent(DataTableInterface $dataTable) : iterable
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

        $qb->select('COUNT('.$this->getTableAs($dataTable).')');

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }


    protected function getTableAs(DataTableInterface $dataTable) : string
    {
        $reflection = new \ReflectionClass($dataTable->getClassName());
        $shortName = $reflection->getShortName();

        return strtolower(substr($shortName, 0, 1));
    }
}
