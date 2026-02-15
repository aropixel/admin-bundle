<?php

namespace Aropixel\AdminBundle\Component\Security\Passport\Badge;

use Aropixel\AdminBundle\Component\Security\EventListener\DisabledUserEventListener;
use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

/**
 * @see DisabledUserEventListener
 *
 * @final
 */
class DisabledUserBadge implements BadgeInterface
{
    public function __construct(
        private readonly UserInterface $user
    ) {
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function isResolved(): bool
    {
        return $this->user->isEnabled();
    }
}
