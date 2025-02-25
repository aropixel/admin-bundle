<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Exception;

use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TooOldPasswordException extends AuthenticationException
{
    public function __construct(
        private readonly UserInterface $user
    ) {
    }

    public function getMessageKey(): string
    {
        return "Vous n'avez pas modifié votre mot de passe depuis trop longtemps.";
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function toHide(): bool
    {
        return true;
    }
}
