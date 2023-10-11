<?php

namespace Aropixel\AdminBundle\Security;

use Aropixel\AdminBundle\Entity\UserInterface;
use Doctrine\ORM\EntityManagerInterface;

class PasswordCreateHandler implements PasswordCreationHandlerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var PasswordUpdater */
    private $passwordUpdater;

    /**
     * @param EntityManagerInterface $em
     * @param PasswordUpdater $passwordUpdater
     */
    public function __construct(EntityManagerInterface $em, PasswordUpdater $passwordUpdater)
    {
        $this->em = $em;
        $this->passwordUpdater = $passwordUpdater;
    }

    public function create(UserInterface $user, string $password)
    {
        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);
        $user->setLastPasswordUpdate(new \DateTime());
        $user->setEnabled(1);

        $user->setPlainPassword($password);
        $this->passwordUpdater->hashPassword($user);

        $this->em->flush();
    }

}
