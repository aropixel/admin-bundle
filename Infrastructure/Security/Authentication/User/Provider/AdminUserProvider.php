<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Authentication\User\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminUserProvider implements AdminUserProviderInterface
{
    private EntityManagerInterface $em;
    private ParameterBagInterface $parameterBag;


    /**
     * @param EntityManagerInterface $em
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {
        $this->em = $em;
        $this->parameterBag = $parameterBag;
    }

    private function getUserClass() : string
    {
        $entities = $this->parameterBag->get('aropixel_admin.entities');
        return $entities[\Aropixel\AdminBundle\Entity\UserInterface::class];
    }

    /**
     * @inheritDoc
     */
    public function refreshUser(UserInterface $user)
    {

        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $reloadedUser = $this->em->getRepository($this->getUserClass())->findOneBy(['email' => $user->getUserIdentifier()]);

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
        $user = $this->em->getRepository($this->getUserClass())->findOneBy(['email' => $identifier]);
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
