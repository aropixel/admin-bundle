<?php


namespace Aropixel\AdminBundle\Infrastructure\Security\Exception;


use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class BlockedUserException extends AuthenticationException
{
    private $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey() : string
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
