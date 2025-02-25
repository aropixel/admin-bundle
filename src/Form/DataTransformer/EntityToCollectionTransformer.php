<?php

namespace Aropixel\AdminBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntityToCollectionTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        $imageCollection = new ArrayCollection();
        $imageCollection->add($value);

        return $imageCollection;
    }

    public function reverseTransform(mixed $value): mixed
    {
        /** @var ArrayCollection $value */
        return $value->first();
    }
}
