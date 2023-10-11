<?php

namespace Aropixel\AdminBundle\Security;

use Aropixel\AdminBundle\Entity\User;

interface ActivationLinkFactoryInterface
{
    public function createActivationLink(User $user) : string;
}
