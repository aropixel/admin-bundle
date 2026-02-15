<?php

namespace Aropixel\AdminBundle\Component\Reset\Request;

use Aropixel\AdminBundle\Entity\UserInterface;

interface RequestLauncherInterface
{
    public function reset(UserInterface $user): void;

    public function cancelRequest(UserInterface $user): void;
}
