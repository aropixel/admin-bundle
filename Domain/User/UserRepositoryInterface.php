<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 01/02/2023 à 16:06
 */

namespace Aropixel\AdminBundle\Domain\User;

use Aropixel\AdminBundle\Entity\UserInterface;
use Doctrine\Persistence\ObjectRepository;

interface UserRepositoryInterface extends ObjectRepository
{
    public function findUserByEmail(string $email) : ?UserInterface;
    public function create(UserInterface $user) : void;
    public function remove(UserInterface $user, bool $flush = false) : void;
}
