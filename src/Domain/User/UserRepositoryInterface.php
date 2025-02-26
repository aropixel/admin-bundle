<?php

namespace Aropixel\AdminBundle\Domain\User;

use Aropixel\AdminBundle\Entity\UserInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<UserInterface>
 */
interface UserRepositoryInterface extends ObjectRepository
{
    public function findUserByEmail(string $email): ?UserInterface;

    public function create(UserInterface $user): void;

    public function remove(UserInterface $user, bool $flush = false): void;
}
