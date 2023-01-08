<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Passport\Badge;


use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\EventListener\TooOldLastLoginEventListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

/**
 * @see TooOldLastLoginEventListener
 *
 * @final
 */
class TooOldLastLoginBadge implements BadgeInterface
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
        if ($this->user->tooOldLastLogin()) {
            return false;
        } else {
            return true;
        }
    }

}
