<?php

namespace Aropixel\AdminBundle\Domain\User;

use Aropixel\AdminBundle\Entity\User;

interface UserFactoryInterface
{
    public function createUser(): User;
}
