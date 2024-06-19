<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Passport\Badge;

use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

/**
 * @see \AdminBundle\Infrastructure\Security\EventListener\TooOldPasswordEventListener
 *
 * @final
 */
class TooOldPasswordBadge implements BadgeInterface
{
    public function __construct(
        private readonly UserInterface $user,
        private readonly string $nbMonths
    ) {
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function isResolved(): bool
    {
        return !$this->user->tooOldPassword($this->nbMonths);
    }
}
