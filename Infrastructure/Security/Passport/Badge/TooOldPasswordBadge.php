<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Passport\Badge;


use Aropixel\AdminBundle\Domain\Entity\User;
use Aropixel\AdminBundle\EventListener\TooOldPasswordEventListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;


/**
 * @see TooOldPasswordEventListener
 *
 * @final
 */
class TooOldPasswordBadge implements BadgeInterface
{

    private User $user;
    private string $nbMonths;


    public function __construct(User $user, string $nbMonths)
    {
        $this->user = $user;
        $this->nbMonths = $nbMonths;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function isResolved(): bool
    {
        $now = new \Datetime('now');
        $lastPasswordUpdate = $this->user->getLastPasswordUpdate() ?: $this->user->getCreatedAt();

        $lastPasswordUpdate = clone($lastPasswordUpdate);
        $lastPasswordUpdate = $lastPasswordUpdate->modify('+'. $this->nbMonths);

        if ($now > $lastPasswordUpdate) {
            return false;
        }
        return true;
    }

}
