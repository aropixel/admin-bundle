<?php

namespace Aropixel\AdminBundle\Component\User;

use Aropixel\AdminBundle\Entity\User;

interface UserFactoryInterface
{
    public function createUser(): User;
}
