<?php

namespace Aropixel\AdminBundle\Infrastructure\Publication\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * @extends ServiceEntityRepository<object>
 */
abstract class PublishableRepository extends ServiceEntityRepository
{
    /**
     * @param array<string,string>|null $orderBy
     */
    public function qbPublished(string $letter, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): QueryBuilder
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

    /**
     * @param array<string,string>|null $orderBy
     */
    public function countPublished(?array $orderBy = null, ?int $limit = null, ?int $offset = null): mixed
    {
        $qb = $this->qbPublished('q', $orderBy, $limit, $offset);
        $qb
            ->select('count(q.id)')
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param array<string,string>|null $orderBy
     */
    public function findPublished(?array $orderBy = null, ?int $limit = null, ?int $offset = null): mixed
    {
        $qb = $this->qbPublished('q', $orderBy, $limit, $offset);
        if (1 == $limit) {
            return $qb->getQuery()->getOneOrNullResult();
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array<mixed> $by
     * @param array<string,string>|null $orderBy
     */
    public function findPublishedBy(array $by, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): mixed
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

    public function findOnePublished(mixed $criteria, string $field = 'slug'): mixed
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
