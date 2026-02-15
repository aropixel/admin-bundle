<?php

namespace Aropixel\AdminBundle\Component\Reset\Request;

use Aropixel\AdminBundle\Entity\UserInterface;

interface ResetLinkFactoryInterface
{
    public function createResetLink(UserInterface $user): string;
}
