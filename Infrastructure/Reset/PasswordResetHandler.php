<?php

namespace Aropixel\AdminBundle\Infrastructure\Reset;

use Aropixel\AdminBundle\Domain\Reset\PasswordResetHandlerInterface;
use Aropixel\AdminBundle\Domain\User\Exception\UnchangedPasswordException;
use Aropixel\AdminBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordResetHandler implements PasswordResetHandlerInterface
{
    private EntityManagerInterface $em;
    private ParameterBagInterface $parameterBag;
    private UserPasswordHasherInterface $userPasswordHasher;


    /**
     * @param EntityManagerInterface $em
     * @param ParameterBagInterface $parameterBag
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->em = $em;
        $this->parameterBag = $parameterBag;
        $this->userPasswordHasher = $userPasswordHasher;
    }


    public function update(User $user, string $password)
    {
        if ($this->shouldChangePassword($user) && $this->userPasswordHasher->isPasswordValid($user, $password)) {
            throw new UnchangedPasswordException();
        }

        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);
        $user->setLastLogin(null);

        $hashPassword = $this->userPasswordHasher->hashPassword($user, $password);
        $user->setPassword($hashPassword);

        $this->em->flush();
    }

    private function shouldChangePassword(User $user) : bool
    {
        return $user->tooOldLastLogin() || $user->tooOldPassword($this->parameterBag->get('passwordPeriod'));
    }
}