<?php

namespace Aropixel\AdminBundle\Domain\User;

use Aropixel\AdminBundle\Entity\UserInterface;

interface PasswordUpdaterInterface
{
    public function hashPlainPassword(UserInterface $user);
}
