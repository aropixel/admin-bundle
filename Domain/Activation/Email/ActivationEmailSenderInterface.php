<?php

namespace Aropixel\AdminBundle\Domain\Activation\Email;

use Aropixel\AdminBundle\Entity\UserInterface;

interface ActivationEmailSenderInterface
{
    public function sendActivationEmail(UserInterface $user);
}
