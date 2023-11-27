<?php


namespace Aropixel\AdminBundle\Infrastructure\Security\Exception;


use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class DisabledUserException extends AuthenticationException
{
    private $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return "Votre compte est désactivé.";
    }

    public function getUser()
    {
        return $this->user;
    }

    public function toHide()
    {
        return false;
    }

}
