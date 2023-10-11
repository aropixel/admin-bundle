<?php

namespace Aropixel\AdminBundle\Security;

use Aropixel\AdminBundle\Entity\UserInterface;

interface PasswordCreationHandlerInterface
{
    public function create(UserInterface $user, string $password);
}
