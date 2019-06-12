<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aropixel\AdminBundle\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * Class RepositoryManager.
 *
 *
 * @author  Joel Gomez <joel.gomez@aropixel.com>
 */
abstract class RepositoryManager extends EntityRepository
{


    /**
     * {@inheritdoc}
     */
    public function qbPublished($letter, array $orderBy = null, $limit = null, $offset = null)
    {
        //
        $qb = $this->createQueryBuilder($letter);

        //
        $qb
            ->where($qb->expr()->orX(
                $qb->expr()->lte($letter.'.publishAt', ':now'),
                $qb->expr()->isNull($letter.'.publishAt')
            ))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->gte($letter.'.publishUntil', ':now'),
                $qb->expr()->isNull($letter.'.publishUntil')
            ))
            ->andWhere($letter.'.status = :status')
            ->setParameter('now', date('Y-m-d H:i:s'))
            ->setParameter('status', 'online');

        if (!is_null($orderBy)) {
            foreach ($orderBy as $field => $direction) {
                $qb->orderBy($letter.'.'.$field, $direction);
            }
        }


        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function findPublished(array $orderBy = null, $limit = null, $offset = null)
    {
        //
        $qb = $this->qbPublished('q', $orderBy, $limit, $offset);
        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findOnePublished($criteria, $field="slug")
    {
        //
        $qb = $this->qbPublished('q');

        $qb
            ->setMaxResults(1)
            ->andWhere('q.'.$field.' = :criteria')
            ->setParameter('criteria', $criteria)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

}
