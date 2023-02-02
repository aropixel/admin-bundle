<?php


namespace Aropixel\AdminBundle\Infrastructure\Security\Exception;


use Aropixel\AdminBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class TooOldLastLoginException extends AuthenticationException
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getMessageKey()
    {
        return "Après plusieurs mois d'inactivité, votre compte a été désactivé.";
    }

    public function getUser()
    {
        return $this->user;
    }

    public function toHide()
    {
        return true;
    }

}
