<?php

namespace Aropixel\AdminBundle\Component\Media\Crop\Listener;

use Aropixel\AdminBundle\Component\Media\Image\Crop\CropApplierInterface;
use Aropixel\AdminBundle\Entity\AttachedImageInterface;
use Aropixel\AdminBundle\Entity\CropInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
class DoFileCropListener
{
    public function __construct(
        private readonly CropApplierInterface $cropper
    ) {
    }

    /**
     * @param LifecycleEventArgs<EntityManager> $args
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->doCrop($args);
    }

    /**
     * @param LifecycleEventArgs<EntityManager> $args
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->doCrop($args);
    }

    /**
     * @param LifecycleEventArgs<EntityManager> $args
     */
    public function doCrop(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof CropInterface) {
            // If there is no crops info registered, we can leave
            $cropCoordinates = $entity->getCrop();
            if (!$cropCoordinates) {
                return;
            }

            /**
             * If there is no image attached or no filename for the image,
             * we leave.
             *
             * @var ?AttachedImageInterface $image
             */
            $image = $entity->getImage();
            if (null === $image || !$image->getFilename()) {
                return;
            }

            $filterName = $entity->getFilter();
            $this->cropper->applyCrop($image->getImage(), $filterName, $cropCoordinates);
        }
    }
}
