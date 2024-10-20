<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 01/04/2019 à 10:37
 */

namespace Aropixel\AdminBundle\Security;


use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class UserManager implements UserManagerInterface
{

    /** @var EntityManagerInterface $em */
    private $em;

    /** @var PasswordInitializerInterface $passwordInitializer */
    private $passwordInitializer;

    /** @var PasswordUpdater $passwordUpdater */
    private $passwordUpdater;

    /** @var ParameterBagInterface */
    private $parameterBag;

    /** @var string */
    private $model = User::class;


    /**
     * UserManager constructor.
     * @param EntityManagerInterface $em
     * @param PasswordInitializerInterface $passwordInitializer
     * @param PasswordUpdater $passwordUpdater
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(EntityManagerInterface $em, PasswordInitializerInterface $passwordInitializer, PasswordUpdater $passwordUpdater, ParameterBagInterface $parameterBag)
    {
        $this->em = $em;
        $this->passwordInitializer = $passwordInitializer;
        $this->passwordUpdater = $passwordUpdater;
        $this->parameterBag = $parameterBag;

        $entities = $this->parameterBag->get('aropixel_admin.entities');
        $this->model = $entities[UserInterface::class];
    }


    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        return $this->em->getRepository($this->model);
    }

    /**
     * {@inheritdoc}
     */
    public function createUser()
    {
        $user = new $this->model();
        $user->setEnabled(false);
        $this->passwordInitializer->createPassword($user);

        return $user;
    }


    /**
     * {@inheritdoc}
     */
    public function findUserByEmail($email)
    {
        return $this->em->getRepository($this->model)->findOneBy(array('email' => $email));
    }


    /**
     * {@inheritdoc}
     */
    public function updatePassword(User $user)
    {
        $this->passwordUpdater->hashPassword($user);
        $user->setLastPasswordUpdate(new \Datetime('now'));
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
