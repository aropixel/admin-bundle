<?php

namespace Aropixel\AdminBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntityToHiddenTransformer implements DataTransformerInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $em;
    private $repository;

    public function __construct(EntityManagerInterface $em, $repository)
    {
        $this->em = $em;
        $this->class = $repository;
    }

    /**
     * @return int
     */
    public function transform(mixed $entity): mixed
    {
        return $entity ? $entity->getId() : false;
    }

    /**
     * @return mixed|object
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform(mixed $id): mixed
    {
        if (!$id) {
            return null;
        }

        $entity = $this->em->getRepository($this->class)->findOneBy(['id' => $id]);

        if (null === $entity) {
            throw new TransformationFailedException(sprintf('A %s with id "%s" does not exist!', $this->repository, $id));
        }

        return $entity;
    }
}
