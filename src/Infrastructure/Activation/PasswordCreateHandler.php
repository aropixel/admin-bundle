<?php

namespace Aropixel\AdminBundle\Infrastructure\Activation;

use Aropixel\AdminBundle\Domain\Activation\PasswordCreationHandlerInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordCreateHandler implements PasswordCreationHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function create(UserInterface $user, string $password)
    {
        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);
        $user->setLastPasswordUpdate(new \DateTime());
        $user->setEnabled(1);

        $hashPassword = $this->userPasswordHasher->hashPassword($user, $password);
        $user->setPassword($hashPassword);

        $this->em->flush();
    }
}
