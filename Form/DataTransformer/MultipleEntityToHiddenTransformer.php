<?php

namespace Aropixel\AdminBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\ORM\EntityManager;

class MultipleEntityToHiddenTransformer implements DataTransformerInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $em;
    private $repository;

    /**
     */
    public function __construct(EntityManagerInterface $em, $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * @param mixed $entity
     *
     * @return integer
     */
    public function transform($collection)
    {
        // Modified from comments to use instanceof so that base classes or interfaces can be specified
        if (null === $collection) {
            return '';
        }

        $ids = array();
        foreach ($collection as $entity) {
            $ids[] = $entity->getId();
        }

        return $ids;

    }

    /**
     * @param mixed $id
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     *
     * @return mixed|object
     */
    public function reverseTransform($array)
    {
        if (!is_array($array)) {
            //updated due to https://github.com/LRotherfield/Form/commit/2be11d1c239edf57de9f6e418a067ea9f1f8c2ed
            return array();
        }

        $collection = array();
        foreach ($array as $id) {
            $entity = $this->em->getRepository($this->repository)->findOneBy(array("id" => $id));
            $collection[] = $entity;
        }

        return $collection;
    }


}
