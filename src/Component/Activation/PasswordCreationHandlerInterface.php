<?php

namespace Aropixel\AdminBundle\Component\Activation;

use Aropixel\AdminBundle\Entity\UserInterface;

interface PasswordCreationHandlerInterface
{
    public function create(UserInterface $user, string $password): void;
}
