<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Crop\Listener;

use Aropixel\AdminBundle\Domain\Media\Image\Crop\CropApplierInterface;
use Aropixel\AdminBundle\Entity\CropInterface;
use Aropixel\AdminBundle\Entity\AttachedImageInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;


class DoFileCropListener
{
    private CropApplierInterface $cropper;

    /**
     * @param CropApplierInterface $cropper
     */
    public function __construct(CropApplierInterface $cropper)
    {
        $this->cropper = $cropper;
    }

    public function postUpdate(LifecycleEventArgs $args) : void
    {
        $this->doCrop($args);
    }

    public function postPersist(LifecycleEventArgs $args) : void
    {
        $this->doCrop($args);
    }

    public function doCrop(LifecycleEventArgs $args) : void
    {

        $entity = $args->getObject();
        if ($entity instanceof CropInterface) {

            // If there is no crops info registered, we can leave
            $cropCoordinates = $entity->getCrop();
            if (!$cropCoordinates)    return;

            /**
             * If there is no image attached or no filename for the image,
             * we leave
             * @var AttachedImageInterface $image
             */
            $image = $entity->getImage();
            if (!$image || !$image->getFilename())    return;

            //
            $filterName = $entity->getFilter();
            $this->cropper->applyCrop($image->getFilename(), $filterName, $cropCoordinates);

        }


    }

}
