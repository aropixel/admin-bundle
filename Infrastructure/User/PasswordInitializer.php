<?php

namespace Aropixel\AdminBundle\Infrastructure\User;

use Aropixel\AdminBundle\Domain\User\PasswordInitializerInterface;
use Aropixel\AdminBundle\Domain\User\PasswordUpdaterInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordInitializer implements PasswordInitializerInterface
{
    public const DEFAULT_PASSWORD = 'cooy!&A?gbFy4tHRR9nC$8?#e7@y?mj6Q37D6iYX';

    public function __construct(
        private readonly PasswordUpdaterInterface $passwordUpdater,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function createPassword(UserInterface $user)
    {
        $user->setPlainPassword(self::DEFAULT_PASSWORD);
        $this->passwordUpdater->hashPlainPassword($user);
    }

    public function stillPendingPasswordCreation(UserInterface $user): bool
    {
        return $this->passwordHasher->isPasswordValid($user, self::DEFAULT_PASSWORD) == $user->getPassword();
    }
}
