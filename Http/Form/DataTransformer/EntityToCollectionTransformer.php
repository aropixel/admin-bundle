<?php

namespace Aropixel\AdminBundle\Http\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntityToCollectionTransformer implements DataTransformerInterface
{


    /**
     * @param mixed $entity
     *
     * @return integer
     */
    public function transform($entity)
    {
        $imageCollection = new ArrayCollection();
        $imageCollection->add($entity);

        return $imageCollection;
    }

    /**
     * @param mixed $id
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     *
     * @return mixed|object
     */
    public function reverseTransform($collection)
    {

        return $collection->first();

    }

}
