<?php

namespace Aropixel\AdminBundle\Infrastructure\Publication\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Class RepositoryManager.
 *
 * @author  Joel Gomez <joel.gomez@aropixel.com>
 */
abstract class PublishableRepository extends ServiceEntityRepository
{
    public function qbPublished($letter, ?array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder($letter);

        $qb
            ->where($qb->expr()->orX(
                $qb->expr()->lte($letter . '.publishAt', ':now'),
                $qb->expr()->isNull($letter . '.publishAt')
            ))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->gte($letter . '.publishUntil', ':now'),
                $qb->expr()->isNull($letter . '.publishUntil')
            ))
            ->andWhere($letter . '.status = :status')
            ->setParameter('now', date('Y-m-d H:i:s'))
            ->setParameter('status', 'online')
        ;

        if (null !== $orderBy) {
            foreach ($orderBy as $field => $direction) {
                $qb->orderBy($letter . '.' . $field, $direction);
            }
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb;
    }

    public function countPublished(?array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->qbPublished('q', $orderBy, $limit, $offset);
        $qb
            ->select('count(q.id)')
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findPublished(?array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->qbPublished('q', $orderBy, $limit, $offset);
        if (1 == $limit) {
            return $qb->getQuery()->getOneOrNullResult();
        }

        return $qb->getQuery()->getResult();
    }

    public function findPublishedBy($by, ?array $orderBy = null, $limit = null, $offset = null)
    {
        $converter = new CamelCaseToSnakeCaseNameConverter();

        $qb = $this->qbPublished('q', $orderBy, $limit, $offset);
        foreach ($by as $key => $value) {
            $qb->andWhere('q.' . $key . ' = :' . $converter->normalize($key));
            $qb->setParameter($converter->normalize($key), $value);
        }

        if (1 == $limit) {
            return $qb->getQuery()->getOneOrNullResult();
        }

        return $qb->getQuery()->getResult();
    }

    public function findOnePublished($criteria, $field = 'slug')
    {
        $qb = $this->qbPublished('q');

        $qb
            ->setMaxResults(1)
            ->andWhere('q.' . $field . ' = :criteria')
            ->setParameter('criteria', $criteria)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
