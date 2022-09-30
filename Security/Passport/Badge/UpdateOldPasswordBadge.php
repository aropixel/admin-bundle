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
class UpdateOldPasswordBadge implements BadgeInterface
{

    private $user;

    private $resolved = false;

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

        $diff = $lastPasswordUpdate->diff($now);
        $yearsInMonths = $diff->format('%r%y') * 12;
        $months = $diff->format('%r%m');
        $totalMonths = $yearsInMonths + intval($months);

        if ($totalMonths > 5) {
            return true;
        }

        $this->markResolved();
        return true;
    }

    public function markResolved(): void
    {
        $this->resolved = true;
    }

}
