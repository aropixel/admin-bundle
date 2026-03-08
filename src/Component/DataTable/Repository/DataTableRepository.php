<?php

namespace Aropixel\AdminBundle\Component\DataTable\Repository;

use Aropixel\AdminBundle\Component\DataTable\DataTableInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class DataTableRepository implements DataTableRepositoryInterface
{
    protected ?string $repositoryMethodName = 'getQueryDataTable';

    /** @var callable[] */
    private array $filters = [];

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function setRepositoryMethod(string $methodName): static
    {
        $this->repositoryMethodName = $methodName;

        return $this;
    }

    public function addFilter(callable $filter): static
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
        $qb = $this->getQueryBuilder($dataTable);

        $query = $qb->getQuery();
        $query->setFirstResult($context->getStart() ?: 0);
        $query->setMaxResults($context->getLength() ?: 50);

        return $query->getResult();
    }

    public function count(DataTableInterface $dataTable): int
    {
        $qb = $this->getQueryBuilder($dataTable);

        $qb->select('COUNT(' . $this->getTableAs($dataTable) . ')');

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    protected function getQueryBuilder(DataTableInterface $dataTable): QueryBuilder
    {
        $context = $dataTable->getContext();

        /** @var class-string $className */
        $className = $dataTable->getClassName();
        $repository = $this->em->getRepository($className);

        // Si une méthode spécifique est demandée ou si la méthode par défaut existe dans le repository
        if ($this->repositoryMethodName && method_exists($repository, $this->repositoryMethodName)) {
            $qb = $repository->{$this->repositoryMethodName}($context);
        } else {
            // Sinon, on construit un QueryBuilder par défaut
            $alias = $this->getTableAs($dataTable);
            $qb = $repository->createQueryBuilder($alias);

            // Gestion de la recherche automatique
            $search = $context->getSearch();
            $searchFields = $dataTable->getSearchFields();

            if (mb_strlen($search) && count($searchFields)) {
                $orX = $qb->expr()->orX();
                foreach ($searchFields as $field) {
                    $orX->add($qb->expr()->like($alias . '.' . $field, ':search'));
                }
                $qb->andWhere($orX);
                $qb->setParameter('search', '%' . $search . '%');
            }

            // Gestion du tri automatique
            $orderColumnIndex = $context->getOrderColumn();
            $columns = $dataTable->getColumns();

            if (isset($columns[$orderColumnIndex])) {
                $column = $columns[$orderColumnIndex];
                $queryField = $column->getQueryField();

                if ($queryField) {
                    $direction = $context->getOrderDirection() ?: 'ASC';
                    $qb->orderBy($alias . '.' . $queryField, $direction);
                }
            }
        }

        // Application des filtres manuels
        foreach ($this->filters as $filter) {
            $filter($qb);
        }

        return $qb;
    }

    protected function getTableAs(DataTableInterface $dataTable): string
    {
        $reflection = new \ReflectionClass($dataTable->getClassName());
        $shortName = $reflection->getShortName();

        return mb_strtolower(mb_substr($shortName, 0, 1));
    }
}
