<?php

namespace Aropixel\AdminBundle\Domain\Reset\Request;

use Aropixel\AdminBundle\Entity\UserInterface;

interface ResetLinkFactoryInterface
{
    public function createResetLink(UserInterface $user) : string;
}