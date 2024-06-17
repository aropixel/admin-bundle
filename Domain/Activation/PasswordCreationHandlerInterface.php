<?php

namespace Aropixel\AdminBundle\Domain\Activation;

use Aropixel\AdminBundle\Entity\UserInterface;

interface PasswordCreationHandlerInterface
{
    public function create(UserInterface $user, string $password);
}
