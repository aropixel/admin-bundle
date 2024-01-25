<?php

namespace Aropixel\AdminBundle\Domain\Reset\Request;

use Aropixel\AdminBundle\Entity\UserInterface;

interface RequestLauncherInterface
{
    public function reset(UserInterface $user);
    public function cancelRequest(UserInterface $user);
}