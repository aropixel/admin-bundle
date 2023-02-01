<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 01/02/2023 à 16:06
 */

namespace Aropixel\AdminBundle\Domain\User;

use Aropixel\AdminBundle\Entity\User;

interface UserRepositoryInterface
{
    public function findUserByEmail(string $email) : ?User;
    public function findOneBy(array $criteria, ?array $orderBy = null);
    public function find($id, $lockMode = null, $lockVersion = null);
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    public function remove(User $user, bool $flush = false) : void;
}
