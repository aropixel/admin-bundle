<?php

namespace Aropixel\AdminBundle\EventListener;

use Aropixel\AdminBundle\Entity\CropInterface;
use Aropixel\AdminBundle\Entity\ImageInterface;
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

            /** @var ImageInterface $image */
            $image = $entity->getImage();
            $filterName = $entity->getFilter();
            $cropCoordinates = $entity->getCrop();

            //
            if (!$cropCoordinates)    return;
            if (!$image->getFilename())    return;

            //
            $this->cropper->applyCrop($image->getFilename(), $filterName, $cropCoordinates);

        }


    }

}
