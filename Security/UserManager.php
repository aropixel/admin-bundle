<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 01/04/2019 à 10:37
 */

namespace Aropixel\AdminBundle\Security;


use Aropixel\AdminBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;


class UserManager
{

    /** @var EntityManagerInterface $em */
    private $em;

    /** @var PasswordUpdater $passwordUpdater */
    private $passwordUpdater;

    /**
     * UserManager constructor.
     * @param PasswordUpdater $passwordUpdater
     */
    public function __construct(EntityManagerInterface $em, PasswordUpdater $passwordUpdater)
    {
        $this->em = $em;
        $this->passwordUpdater = $passwordUpdater;
    }


    /**
     * {@inheritdoc}
     */
    public function findUserByEmail($email)
    {
        return $this->em->getRepository(User::class)->findOneBy(array('email' => $email));
    }


    /**
     * {@inheritdoc}
     */
    public function updatePassword(User $user)
    {
        $this->passwordUpdater->hashPassword($user);
    }


    /**
     * {@inheritdoc}
     */
    public function updateUser(User $user, $andFlush = true)
    {
        $this->updatePassword($user);

        $this->em->persist($user);
        if ($andFlush) {
            $this->em->flush();
        }
    }

}
