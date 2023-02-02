<?php

namespace Aropixel\AdminBundle\Domain\Reset\Email;

use Aropixel\AdminBundle\Entity\User;

interface ResetEmailSenderInterface
{
    public function sendResetEmail(User $user, string $resetLink);
}