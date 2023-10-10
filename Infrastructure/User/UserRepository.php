<?php

namespace Aropixel\AdminBundle\Infrastructure\User;

use Aropixel\AdminBundle\Domain\Activation\Email\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Domain\Activation\Request\ActivationLinkFactoryInterface;
use Aropixel\AdminBundle\Domain\User\PasswordInitializerInterface;
use Aropixel\AdminBundle\Domain\User\PasswordUpdaterInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Infrastructure\Reset\Token\UniqueTokenGenerator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    private PasswordInitializerInterface $passwordInitializer;


    public function __construct(
        ManagerRegistry $registry,
        ParameterBagInterface $parameterBag,
        PasswordInitializerInterface $passwordInitializer
    )
    {
        $entitiesClassNames = $parameterBag->get('aropixel_admin.entities');
        parent::__construct($registry, $entitiesClassNames[UserInterface::class]);

        $this->passwordInitializer = $passwordInitializer;
    }

    public function findUserByEmail(string $email): ?UserInterface
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function create(UserInterface $user): void
    {
        $user->setEnabled(false);
        $this->passwordInitializer->createPassword($user);

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();
    }

    public function remove(UserInterface $user, bool $flush = false) : void
    {
        $this->getEntityManager()->remove($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
