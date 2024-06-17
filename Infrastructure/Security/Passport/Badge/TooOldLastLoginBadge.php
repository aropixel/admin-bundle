<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Passport\Badge;

use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Infrastructure\Security\EventListener\TooOldLastLoginEventListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

/**
 * @see TooOldLastLoginEventListener
 *
 * @final
 */
class TooOldLastLoginBadge implements BadgeInterface
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
        return !$this->user->tooOldLastLogin();
    }
}
