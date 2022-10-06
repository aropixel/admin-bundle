<?php

namespace Aropixel\AdminBundle\Security\Passport\Badge;


use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\EventListener\TooOldLastLoginEventListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

/**
 * @see TooOldLastLoginEventListener
 *
 * @final
 */
class BlockedUserBadge implements BlockedUserBadgeInterface
{

    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function isResolved(): bool
    {
        return !$this->user->isBlocked();
    }

}
