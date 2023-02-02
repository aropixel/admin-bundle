<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 01/02/2023 à 16:46
 */

namespace Aropixel\AdminBundle\Infrastructure\User;

use Aropixel\AdminBundle\Domain\User\UserFactoryInterface;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserFactory implements UserFactoryInterface
{
    private ParameterBagInterface $parameterBag;

    /**
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }


    public function createUser(): User
    {
        $entities = $this->parameterBag->get('aropixel_admin.entities');
        return new $entities[UserInterface::class]();
    }
}
