<?php

namespace Aropixel\AdminBundle\Domain\User;

use Aropixel\AdminBundle\Entity\UserInterface;

interface PasswordInitializerInterface
{
    public function createPassword(UserInterface $user): void;

    public function stillPendingPasswordCreation(UserInterface $user): bool;
}
