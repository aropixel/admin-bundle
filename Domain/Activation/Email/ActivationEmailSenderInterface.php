<?php

namespace Aropixel\AdminBundle\Domain\Activation\Email;

use Aropixel\AdminBundle\Entity\User;

interface ActivationEmailSenderInterface
{
    public function sendActivationEmail(User $user, string $creationLink);
}