<?php

namespace Aropixel\AdminBundle\Repository;

use Aropixel\AdminBundle\Component\User\PasswordInitializerInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @extends ServiceEntityRepository<UserInterface>
 */
#[AutoconfigureTag('doctrine.repository_service')]
#[AsAlias(UserRepositoryInterface::class)]
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        ParameterBagInterface $parameterBag,
        private readonly PasswordInitializerInterface $passwordInitializer
    ) {
        $entitiesClassNames = $parameterBag->get('aropixel_admin.entities');

        /** @var class-string<UserInterface> $className */
        $className = $entitiesClassNames[UserInterface::class];

        // Vérification stricte pour éviter l'erreur
        if (!is_subclass_of($className, UserInterface::class)) {
            throw new \InvalidArgumentException("$className doit implémenter UserInterface.");
        }

        parent::__construct($registry, $className);
    }

    public function findUserByEmail(string $email): ?UserInterface
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function create(UserInterface $user): void
    {
        $user->setEnabled((bool) $user->getPlainPassword());
        $this->passwordInitializer->createPassword($user);

        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();
    }

    public function remove(UserInterface $user, bool $flush = false): void
    {
        $this->getEntityManager()->remove($user);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
