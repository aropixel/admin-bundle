<?php

namespace Aropixel\AdminBundle\Security\Passport\Badge;


use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\EventListener\TooOldPasswordEventListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

/**
 * @see TooOldPasswordEventListener
 *
 * @final
 */
class TooOldPasswordBadge implements BadgeInterface
{

    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function isResolved(): bool
    {
        $now = new \Datetime('now');
        $lastPasswordUpdate = $this->user->getLastPasswordUpdate() ?: $this->user->getCreatedAt();

        $lastPasswordUpdate = clone($lastPasswordUpdate);
        $lastPasswordUpdate = $lastPasswordUpdate->modify('+6 month');

        if ($now > $lastPasswordUpdate) {
            return false;
        }
        return true;
    }

}
