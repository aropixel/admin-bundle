<?php

namespace Aropixel\AdminBundle\Infrastructure\User;

use Aropixel\AdminBundle\Domain\User\UserFactoryInterface;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserFactory implements UserFactoryInterface
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    public function createUser(): User
    {
        $entities = $this->parameterBag->get('aropixel_admin.entities');

        return new $entities[UserInterface::class]();
    }
}
