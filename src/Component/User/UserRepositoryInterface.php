<?php

namespace Aropixel\AdminBundle\Component\User;

use Aropixel\AdminBundle\Entity\UserInterface;
use Doctrine\DBAL\LockMode;

interface UserRepositoryInterface
{
    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?object;

    /**
     * @param array<string, mixed> $criteria The criteria.
     * @return object|null The object.
     * @psalm-return UserInterface|null
     */
    public function findOneBy(array $criteria): ?object;

    /**
     * @return array<int, object> The objects.
     * @psalm-return UserInterface[]
     */
    public function findAll(): array;

    /**
     * @param array<string, mixed>       $criteria
     * @param array<string, string>|null $orderBy
     * @psalm-param array<string, 'asc'|'desc'|'ASC'|'DESC'>|null $orderBy
     * @return array<int, object> The objects.
     * @psalm-return UserInterface[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;


    public function findUserByEmail(string $email): ?UserInterface;

    public function create(UserInterface $user): void;

    public function remove(UserInterface $user, bool $flush = false): void;
}
