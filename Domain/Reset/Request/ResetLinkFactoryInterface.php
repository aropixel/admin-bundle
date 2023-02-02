<?php

namespace Aropixel\AdminBundle\Domain\Reset\Request;

use Aropixel\AdminBundle\Entity\User;

interface ResetLinkFactoryInterface
{
    public function createResetLink(User $user) : string;

}