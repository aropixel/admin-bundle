<?php

namespace Aropixel\AdminBundle\Component\Activation\Request;

use Aropixel\AdminBundle\Entity\UserInterface;

interface ActivationLinkFactoryInterface
{
    public function createActivationLink(UserInterface $user): string;
}
