<?php

namespace Aropixel\AdminBundle\Domain\Reset;

use Aropixel\AdminBundle\Entity\UserInterface;

interface PasswordResetHandlerInterface
{
    public function update(UserInterface $user, string $password);
}