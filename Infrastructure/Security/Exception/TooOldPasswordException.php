<?php


namespace Aropixel\AdminBundle\Infrastructure\Security\Exception;


use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TooOldPasswordException extends AuthenticationException
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
        return "Vous n'avez pas modifié votre mot de passe depuis trop longtemps.";
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