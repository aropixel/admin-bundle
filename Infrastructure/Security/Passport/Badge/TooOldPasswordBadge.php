<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Passport\Badge;


use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Infrastructure\Security\EventListener\TooOldPasswordEventListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;


/**
 * @see \AdminBundle\Infrastructure\Security\EventListener\TooOldPasswordEventListener
 *
 * @final
 */
class TooOldPasswordBadge implements BadgeInterface
{

    private UserInterface $user;
    private string $nbMonths;


    public function __construct(UserInterface $user, string $nbMonths)
    {
        $this->user = $user;
        $this->nbMonths = $nbMonths;
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
