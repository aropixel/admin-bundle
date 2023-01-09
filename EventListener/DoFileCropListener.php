<?php

namespace Aropixel\AdminBundle\EventListener;

use Aropixel\AdminBundle\Domain\Entity\CropInterface;
use Aropixel\AdminBundle\Domain\Entity\ImageInterface;
use Aropixel\AdminBundle\Image\Cropper;
use Doctrine\ORM\Event\LifecycleEventArgs;


class DoFileCropListener
{
    /** @var Cropper  */
    private $cropper;

    /**
     */
    public function __construct(Cropper $cropper)
    {
        $this->cropper = $cropper;
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->doCrop($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->doCrop($args);
    }

    public function doCrop(LifecycleEventArgs $args)
    {

        //
        $entity = $args->getEntity();

        //
        if ($entity instanceof CropInterface) {

            // If there is no crops info registered, we can leave
            $cropCoordinates = $entity->getCrop();
            if (!$cropCoordinates)    return;

            /**
             * If there is no image attached or no filename for the image,
             * we leave
             * @var \Aropixel\AdminBundle\Domain\Entity\ImageInterface $image
             */
            $image = $entity->getImage();
            if (!$image || !$image->getFilename())    return;

            //
            $filterName = $entity->getFilter();
            $this->cropper->applyCrop($image->getFilename(), $filterName, $cropCoordinates);

        }


    }

}
