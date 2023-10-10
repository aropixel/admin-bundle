<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 01/02/2023 à 16:06
 */

namespace Aropixel\AdminBundle\Domain\User;

use Aropixel\AdminBundle\Entity\UserInterface;

interface UserRepositoryInterface
{
    public function findUserByEmail(string $email) : ?UserInterface;
    public function findOneBy(array $criteria, ?array $orderBy = null);
    public function find($id, $lockMode = null, $lockVersion = null);
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    public function create(UserInterface $user) : void;
    public function remove(UserInterface $user, bool $flush = false) : void;
}
