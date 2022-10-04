<?php


namespace Aropixel\AdminBundle\Security\Exception;


use Aropixel\AdminBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class BlockedUserException extends AuthenticationException
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return "Suite à de trop nombreuses tentatives de connexion échouées, votre compte a été désactivé.";
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
