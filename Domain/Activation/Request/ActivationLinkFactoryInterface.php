<?php

namespace Aropixel\AdminBundle\Domain\Activation\Request;

use Aropixel\AdminBundle\Entity\UserInterface;

interface ActivationLinkFactoryInterface
{
    public function createActivationLink(UserInterface $user) : string;
}