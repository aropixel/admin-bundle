<?php

namespace Aropixel\AdminBundle\Component\Reset;

use Aropixel\AdminBundle\Entity\UserInterface;

interface PasswordResetHandlerInterface
{
    public function update(UserInterface $user, string $password): void;
}
