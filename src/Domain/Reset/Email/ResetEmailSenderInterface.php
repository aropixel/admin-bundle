<?php

namespace Aropixel\AdminBundle\Domain\Reset\Email;

use Aropixel\AdminBundle\Entity\UserInterface;

interface ResetEmailSenderInterface
{
    public function sendResetEmail(UserInterface $user, string $resetLink);
}
