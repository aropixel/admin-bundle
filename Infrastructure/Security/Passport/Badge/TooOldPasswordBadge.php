<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Passport\Badge;


use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Infrastructure\Security\EventListener\TooOldPasswordEventListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;


/**
 * @see \Aropixel\AdminBundle\Infrastructure\Security\EventListener\TooOldPasswordEventListener
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
        return !$this->user->tooOldPassword($this->nbMonths);
    }

}
