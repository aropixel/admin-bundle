<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/10/2023 à 16:18
 */

namespace Aropixel\AdminBundle\Security;

use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordInitializer implements PasswordInitializerInterface
{
    const DEFAULT_PASSWORD = "cooy!&A?gbFy4tHRR9nC$8?#e7@y?mj6Q37D6iYX";

    private $passwordUpdater;
    private $passwordHasher;

    /**
     * @param PasswordUpdater $passwordUpdater
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(PasswordUpdater $passwordUpdater, UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordUpdater = $passwordUpdater;
        $this->passwordHasher = $passwordHasher;
    }


    public function createPassword(UserInterface $user)
    {
        $user->setPlainPassword(self::DEFAULT_PASSWORD);
        $this->passwordUpdater->hashPassword($user);
    }

    public function stillPendingPasswordCreation(UserInterface $user): bool
    {
        return $this->passwordHasher->isPasswordValid($user, self::DEFAULT_PASSWORD) == $user->getPassword();
    }


}
