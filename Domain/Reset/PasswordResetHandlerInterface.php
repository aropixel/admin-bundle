<?php

namespace Aropixel\AdminBundle\Domain\Reset;

use Aropixel\AdminBundle\Entity\User;

interface PasswordResetHandlerInterface
{
    public function update(User $user, string $password);
}