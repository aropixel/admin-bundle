<?php

namespace Aropixel\AdminBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<mixed, ArrayCollection>
 */
class EntityToCollectionTransformer implements DataTransformerInterface
{
    /**
     * @return ArrayCollection<int,mixed>
     */
    public function transform(mixed $value): ArrayCollection
    {
        $imageCollection = new ArrayCollection();
        $imageCollection->add($value);

        return $imageCollection;
    }

    /**
     * @param ArrayCollection<int,mixed> $value
     */
    public function reverseTransform(mixed $value): mixed
    {
        return $value->first();
    }
}
