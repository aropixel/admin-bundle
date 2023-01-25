<?php

namespace Aropixel\AdminBundle\Domain\Reset\Request;

use Aropixel\AdminBundle\Entity\User;

interface RequestLauncherInterface
{
    public function reset(User $user);
    public function cancelRequest(User $user);
}