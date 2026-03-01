<?php

namespace Aropixel\AdminBundle\Repository;

use Aropixel\AdminBundle\Component\DataTable\Context\DataTableContext;
use Aropixel\AdminBundle\Component\Media\Resolver\ClassNameResolverInterface;
use Aropixel\AdminBundle\Entity\FileInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileInterface>
 */
class FileRepository extends ServiceEntityRepository implements FileRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, ClassNameResolverInterface $classNameResolver)
    {
        parent::__construct($registry, $classNameResolver->getFileClassName());
    }

    public function getQueryDataTable(DataTableContext $context): QueryBuilder
    {
        $qb = $this->createQueryBuilder('f');

        if (mb_strlen($context->getSearch())) {
            $qb->where($qb->expr()->orX(
                $qb->expr()->like('f.title', ':search')
            ));
            $qb->setParameter('search', '%' . $context->getSearch() . '%');
        }

        $qb->orderBy('f.createdAt', 'DESC');

        return $qb;
    }

    public function getCategoryQueryDataTable(DataTableContext $context): QueryBuilder
    {
        $qb = $this->getQueryDataTable($context);
        $qb
            ->andWhere('f.category = :category')
            ->setParameter('category', $context->getAdditionalParameter('category'))
        ;

        return $qb;
    }
}
