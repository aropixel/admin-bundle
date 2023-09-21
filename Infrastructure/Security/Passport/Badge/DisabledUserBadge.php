<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Passport\Badge;


use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Infrastructure\Security\EventListener\DisabledUserEventListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

/**
 * @see DisabledUserEventListener
 *
 * @final
 */
class DisabledUserBadge implements BadgeInterface
{

    private UserInterface $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
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
