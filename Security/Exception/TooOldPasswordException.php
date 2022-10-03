<?php


namespace Aropixel\AdminBundle\Security\Exception;


use Aropixel\AdminBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TooOldPasswordException extends AuthenticationException
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
        return "Vous n'avez pas modifié votre mot de passe depuis trop longtemps.";
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
