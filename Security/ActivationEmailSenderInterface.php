<?php

namespace Aropixel\AdminBundle\Security;

use Aropixel\AdminBundle\Entity\User;

interface ActivationEmailSenderInterface
{
    public function sendActivationEmail(User $user);
}
