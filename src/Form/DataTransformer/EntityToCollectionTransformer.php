<?php

namespace Aropixel\AdminBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntityToCollectionTransformer implements DataTransformerInterface
{
    /**
     * @return int
     */
    public function transform($entity)
    {
        $imageCollection = new ArrayCollection();
        $imageCollection->add($entity);

        return $imageCollection;
    }

    /**
     * @return mixed|object
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform($collection)
    {
        return $collection->first();
    }
}
