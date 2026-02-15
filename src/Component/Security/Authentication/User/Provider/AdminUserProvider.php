<?php

namespace Aropixel\AdminBundle\Component\Security\Authentication\User\Provider;

use Aropixel\AdminBundle\Entity\UserInterface as AropixelUserInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminUserProvider implements AdminUserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    protected function getUserClass(): string
    {
        $entities = $this->parameterBag->get('aropixel_admin.entities');

        return $entities[AropixelUserInterface::class];
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        /**
         * Set the new hashed password on the User object
         * @var AropixelUserInterface $user
         */
        $user->setPassword($newHashedPassword);
        $this->managerRegistry->getManagerForClass($this->getUserClass())->flush();
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        /**
         * @var AropixelUserInterface $user
         */

        if (!$this->supportsClass($user::class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        /** @var class-string $userClass */
        $userClass = $this->getUserClass();
        $repository = $this->managerRegistry->getRepository($userClass);
        $reloadedUser = $repository->findOneBy(['email' => $user->getUserIdentifier()]);

        if (null === $reloadedUser) {
            throw new UserNotFoundException(sprintf('User with ID "%d" could not be refreshed.', $user->getId()));
        }

        return $reloadedUser;
    }

    public function supportsClass(string $class): bool
    {
        return $class == $this->getUserClass();
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        /** @var class-string $userClass */
        $userClass = $this->getUserClass();
        $repository = $this->managerRegistry->getRepository($userClass);
        $user = $repository->findOneBy(['email' => $identifier]);
        if (null === $user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function loadUserByUsername(string $identifier): UserInterface
    {
        return $this->loadUserByIdentifier($identifier);
    }
}
