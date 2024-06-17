<?php

namespace Aropixel\AdminBundle\Domain\User;

use Aropixel\AdminBundle\Entity\UserInterface;

interface PasswordInitializerInterface
{
    public function createPassword(UserInterface $user);

    public function stillPendingPasswordCreation(UserInterface $user): bool;
}
