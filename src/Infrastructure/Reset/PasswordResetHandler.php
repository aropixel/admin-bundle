<?php

namespace Aropixel\AdminBundle\Infrastructure\Reset;

use Aropixel\AdminBundle\Domain\Reset\PasswordResetHandlerInterface;
use Aropixel\AdminBundle\Domain\User\Exception\UnchangedPasswordException;
use Aropixel\AdminBundle\Entity\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordResetHandler implements PasswordResetHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ParameterBagInterface $parameterBag,
        private readonly UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function update(UserInterface $user, string $password)
    {
        if ($this->shouldChangePassword($user) && $this->userPasswordHasher->isPasswordValid($user, $password)) {
            throw new UnchangedPasswordException();
        }

        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);
        $user->setLastLogin(null);
        $user->setLastPasswordUpdate(new \DateTime());

        $hashPassword = $this->userPasswordHasher->hashPassword($user, $password);
        $user->setPassword($hashPassword);

        $this->em->flush();
    }

    private function shouldChangePassword(UserInterface $user): bool
    {
        return $user->tooOldLastLogin() || $user->tooOldPassword($this->parameterBag->get('passwordPeriod'));
    }
}
