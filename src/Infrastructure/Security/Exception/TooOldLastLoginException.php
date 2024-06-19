<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Exception;

use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TooOldLastLoginException extends AuthenticationException
{
    public function __construct(
        private readonly UserInterface $user
    ) {
    }

    public function getMessageKey(): string
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
