<?php

namespace Aropixel\AdminBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class MultipleEntityToHiddenTransformer implements DataTransformerInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $em;

    public function __construct(
        EntityManagerInterface $em,
        private $repository
    ) {
        $this->em = $em;
    }

    /**
     * @return int
     */
    public function transform($collection)
    {
        // Modified from comments to use instanceof so that base classes or interfaces can be specified
        if (null === $collection) {
            return '';
        }

        $ids = [];
        foreach ($collection as $entity) {
            $ids[] = $entity->getId();
        }

        return $ids;
    }

    /**
     * @return mixed|object
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform($array)
    {
        if (!\is_array($array)) {
            // updated due to https://github.com/LRotherfield/Form/commit/2be11d1c239edf57de9f6e418a067ea9f1f8c2ed
            return [];
        }

        $collection = [];
        foreach ($array as $id) {
            $entity = $this->em->getRepository($this->repository)->findOneBy(['id' => $id]);
            $collection[] = $entity;
        }

        return $collection;
    }
}
