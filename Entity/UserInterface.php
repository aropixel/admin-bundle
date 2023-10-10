<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 24/10/2019 à 11:40
 */

namespace Aropixel\AdminBundle\Entity;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;


interface UserInterface extends SymfonyUserInterface, PasswordAuthenticatedUserInterface, PasswordHasherAwareInterface
{

    public function setEnabled(bool $boolean);

    public function setPassword(string $password);

    public function getPlainPassword();

    public function tooOldPassword(string $delay) : bool;

    public function tooOldLastLogin() : bool;

    public function setLastPasswordUpdate(\DateTime $lastPasswordUpdate): void;

}
