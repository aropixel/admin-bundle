<?php

namespace Aropixel\AdminBundle\Infrastructure\User;

use Aropixel\AdminBundle\Entity\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserRepositoryProvider
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

    public function getUserRepository() : EntityRepository
    {
        $entities = $this->parameterBag->get('aropixel_admin.entities');
        $this->em->getRepository($entities[UserInterface::class]);
    }
}