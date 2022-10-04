<?php

namespace Aropixel\AdminBundle\Security\Passport\Badge;


use Aropixel\AdminBundle\Entity\User;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;


interface BlockedUserBadgeInterface extends BadgeInterface
{

    public function getUser(): User;

    public function isResolved(): bool;

}