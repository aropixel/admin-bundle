<?php

namespace Aropixel\AdminBundle\Repository;

use Aropixel\AdminBundle\Entity\UserInterface;
use Doctrine\DBAL\LockMode;

interface UserRepositoryInterface
{
    /**
     * Sans types de retour natifs sur find/findOneBy/findAll/findBy : ORM 2.20 ne les déclare
     * pas sur EntityRepository, ORM 3 les déclare (covariants). L'interface doit rester
     * implémentable par les deux.
     *
     * @return object|null
     */
    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null);

    /**
     * @param array<string, mixed> $criteria the criteria
     *
     * @return object|null the object
     *
     * @psalm-return UserInterface|null
     */
    public function findOneBy(array $criteria);

    /**
     * @return array<int, object> the objects
     *
     * @psalm-return UserInterface[]
     */
    public function findAll();

    /**
     * @param array<string, mixed>       $criteria
     * @param array<string, string>|null $orderBy
     *
     * @psalm-param array<string, 'asc'|'desc'|'ASC'|'DESC'>|null $orderBy
     *
     * @return array<int, object> the objects
     *
     * @psalm-return UserInterface[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null);

    public function findUserByEmail(string $email): ?UserInterface;

    public function create(UserInterface $user): void;

    public function remove(UserInterface $user, bool $flush = false): void;
}
