<?php

namespace Aropixel\AdminBundle\Component\Activation\Email;

use Aropixel\AdminBundle\Entity\UserInterface;

interface ActivationEmailSenderInterface
{
    public function sendActivationEmail(UserInterface $user): void;
}
