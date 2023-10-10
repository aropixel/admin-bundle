<?php

namespace Aropixel\AdminBundle\Domain\Activation\Request;

use Aropixel\AdminBundle\Entity\User;

interface ActivationLinkFactoryInterface
{
    public function createActivationLink(User $user) : string;
}