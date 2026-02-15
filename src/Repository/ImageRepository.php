<?php

namespace Aropixel\AdminBundle\Repository;

use Aropixel\AdminBundle\Component\DataTable\Context\DataTableContext;
use Aropixel\AdminBundle\Component\Media\Resolver\ClassNameResolverInterface;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ImageInterface>
 */
class ImageRepository extends ServiceEntityRepository implements ImageRepositoryInterface
{
    public function __construct(ManagerRegistry $registry, ClassNameResolverInterface $classNameResolver)
    {
        parent::__construct($registry, $classNameResolver->getImageClassName());
    }

    public function getQueryDataTable(DataTableContext $context): QueryBuilder
    {
        $qb = $this->createQueryBuilder('i');

        if (mb_strlen($context->getSearch())) {
            $qb->where($qb->expr()->orX(
                $qb->expr()->like('i.title', ':search')
            ));
            $qb->setParameter('search', '%' . $context->getSearch() . '%');
        }

        $qb->orderBy('i.createdAt', 'DESC');

        return $qb;
    }

    public function getCategoryQueryDataTable(DataTableContext $context): QueryBuilder
    {
        $qb = $this->getQueryDataTable($context);
        $qb
            ->andWhere('i.category = :category')
            ->setParameter('category', $context->getAdditionalParameter('category'))
        ;

        return $qb;
    }

}
