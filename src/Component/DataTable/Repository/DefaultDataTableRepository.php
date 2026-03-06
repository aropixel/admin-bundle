<?php

namespace Aropixel\AdminBundle\Component\DataTable\Repository;

use Aropixel\AdminBundle\Component\DataTable\DataTableInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class DefaultDataTableRepository implements DataTableRepositoryInterface
{
    protected string $repositoryMethodName = 'getQueryDataTable';

    /** @var callable[] */
    private array $filters = [];

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function setRepositoryMethod(string $methodName): self
    {
        $this->repositoryMethodName = $methodName;

        return $this;
    }

    public function addFilter(callable $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getRowsContent(DataTableInterface $dataTable): iterable
    {
        $context = $dataTable->getContext();

        /** @var class-string $className */
        $className = $dataTable->getClassName();
        $repository = $this->em->getRepository($className);

        /** @var QueryBuilder $qb */
        $qb = $repository->{$this->repositoryMethodName}($context);

        foreach ($this->filters as $filter) {
            $filter($qb);
        }

        $query = $qb->getQuery();
        $query->setFirstResult($context->getStart() ?: 0);
        $query->setMaxResults($context->getLength() ?: 50);

        return $query->getResult();
    }

    public function count(DataTableInterface $dataTable): int
    {
        $context = $dataTable->getContext();

        /** @var QueryBuilder $qb */
        $qb = $this->em->getRepository($dataTable->getClassName())->{$this->repositoryMethodName}($context);

        foreach ($this->filters as $filter) {
            $filter($qb);
        }

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
