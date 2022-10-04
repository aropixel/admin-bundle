<?php

namespace Aropixel\AdminBundle\Security\Passport\Badge;


use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\EventListener\TooOldPasswordEventListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


/**
 * @see TooOldPasswordEventListener
 *
 * @final
 */
class TooOldPasswordBadge implements TooOldPasswordBadgeInterface
{

    /** @var ParameterBagInterface */
    private $parameterBag;

    private $user;

    public function __construct(User $user, ParameterBagInterface $parameterBag)
    {
        $this->user = $user;
        $this->parameterBag = $parameterBag;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    protected function getMonths()
    {
        return $this->parameterBag->get('passwordPeriod');
    }

    public function isResolved(): bool
    {
        $now = new \Datetime('now');
        $lastPasswordUpdate = $this->user->getLastPasswordUpdate() ?: $this->user->getCreatedAt();

        $lastPasswordUpdate = clone($lastPasswordUpdate);
        $monthsQty = $this->getMonths();
        $lastPasswordUpdate = $lastPasswordUpdate->modify('+'. $monthsQty);

        if ($now > $lastPasswordUpdate) {
            return false;
        }
        return true;
    }

}
