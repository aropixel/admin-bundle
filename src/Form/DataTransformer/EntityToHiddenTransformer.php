<?php

namespace Aropixel\AdminBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<mixed, mixed>
 */
class EntityToHiddenTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string $class
    ) {
    }

    /**
     * @return string|null
     */
    public function transform(mixed $value): ?string
    {
        return $value ? (string)$value->getId() : null;
    }

    /**
     * @return mixed|object
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform(mixed $value): mixed
    {
        if (null === $value || '' === $value) {
            return null;
        }

        $entity = $this->em->getRepository($this->class)->findOneBy(['id' => $value]);

        if (null === $entity) {
            throw new TransformationFailedException(sprintf('A %s with id "%s" does not exist!', $this->class, $value));
        }

        return $entity;
    }
}
