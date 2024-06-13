<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Authentication\User\Provider;

use Aropixel\AdminBundle\Infrastructure\Security\Authentication\User\Provider\AdminUserProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminUserProvider implements AdminUserProviderInterface, PasswordUpgraderInterface
{
    private ManagerRegistry $managerRegistry;
    private ParameterBagInterface $parameterBag;


    /**
     * @param ManagerRegistry $managerRegistry
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ManagerRegistry $managerRegistry, ParameterBagInterface $parameterBag)
    {
        $this->managerRegistry = $managerRegistry;
        $this->parameterBag = $parameterBag;
    }


    protected function getUserClass() : string
    {
        $entities = $this->parameterBag->get('aropixel_admin.entities');
        return $entities[\Aropixel\AdminBundle\Entity\UserInterface::class];
    }


    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // set the new hashed password on the User object
        $user->setPassword($newHashedPassword);
        $this->managerRegistry->getManagerForClass($this->getUserClass())->flush();
    }


    /**
     * @inheritDoc
     */
    public function refreshUser(UserInterface $user) : UserInterface
    {

        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $reloadedUser = $this->managerRegistry->getRepository($this->getUserClass())->findOneBy(['email' => $user->getUserIdentifier()]);

        if (null === $reloadedUser) {
            throw new UserNotFoundException(
                sprintf('User with ID "%d" could not be refreshed.', $user->getId())
            );
        }

        return $reloadedUser;
    }

    /**
     * @inheritDoc
     */
    public function supportsClass(string $class) : bool
    {
        return $class == $this->getUserClass();
    }

    /**
     * @inheritDoc
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->managerRegistry->getRepository($this->getUserClass())->findOneBy(['email' => $identifier]);
        if (is_null($user)) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function loadUserByUsername(string $identifier): UserInterface
    {
        return $this->loadUserByIdentifier($identifier);
    }


}
