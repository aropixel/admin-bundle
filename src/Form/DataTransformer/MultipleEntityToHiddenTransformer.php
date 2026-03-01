<?php

namespace Aropixel\AdminBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<mixed, mixed>
 */
class MultipleEntityToHiddenTransformer implements DataTransformerInterface
{
    /**
     * @param class-string $repository
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string $repository
    ) {
    }

    /**
     * @param array<mixed>|null $value
     *
     * @return array<mixed>
     */
    public function transform(mixed $value): array
    {
        // Modified from comments to use instanceof so that base classes or interfaces can be specified
        if (null === $value) {
            return [];
        }

        $ids = [];
        foreach ($value as $entity) {
            $ids[] = $entity->getId();
        }

        return $ids;
    }

    /**
     * @return array<mixed>
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform(mixed $value): array
    {
        if (!\is_array($value)) {
            // updated due to https://github.com/LRotherfield/Form/commit/2be11d1c239edf57de9f6e418a067ea9f1f8c2ed
            return [];
        }

        $collection = [];
        foreach ($value as $id) {
            $entity = $this->em->getRepository($this->repository)->findOneBy(['id' => $id]);
            $collection[] = $entity;
        }

        return $collection;
    }
}
