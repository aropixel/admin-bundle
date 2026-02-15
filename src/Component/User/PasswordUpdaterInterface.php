<?php

namespace Aropixel\AdminBundle\Component\User;

use Aropixel\AdminBundle\Entity\UserInterface;

interface PasswordUpdaterInterface
{
    public function hashPlainPassword(UserInterface $user): void;
}
