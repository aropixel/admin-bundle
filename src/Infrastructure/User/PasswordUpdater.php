<?php

namespace Aropixel\AdminBundle\Infrastructure\User;

use Aropixel\AdminBundle\Domain\User\PasswordUpdaterInterface;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordUpdater implements PasswordUpdaterInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function hashPlainPassword(UserInterface $user): void
    {
        $plainPassword = $user->getPlainPassword();

        if (0 === mb_strlen((string) $plainPassword)) {
            return;
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $user->setLastPasswordUpdate(new \DateTime());
        $user->eraseCredentials();
    }
}
