<?php

namespace Aropixel\AdminBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntityToHiddenTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string $class
    ) {
    }

    /**
     * @return int
     */
    public function transform(mixed $value): mixed
    {
        return $value ? $value->getId() : false;
    }

    /**
     * @return mixed|object
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform(mixed $value): mixed
    {
        if (!$value) {
            return null;
        }

        $entity = $this->em->getRepository($this->class)->findOneBy(['id' => $value]);

        if (null === $entity) {
            throw new TransformationFailedException(sprintf('A %s with id "%s" does not exist!', $this->class, $value));
        }

        return $entity;
    }
}
