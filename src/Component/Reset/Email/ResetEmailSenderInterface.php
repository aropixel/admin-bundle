<?php

namespace Aropixel\AdminBundle\Component\Reset\Email;

use Aropixel\AdminBundle\Entity\UserInterface;

interface ResetEmailSenderInterface
{
    public function sendResetEmail(UserInterface $user, string $resetLink): void;
}
