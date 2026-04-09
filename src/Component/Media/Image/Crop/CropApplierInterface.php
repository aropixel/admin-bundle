<?php

namespace Aropixel\AdminBundle\Component\Media\Image\Crop;

use Aropixel\AdminBundle\Entity\ImageInterface;

interface CropApplierInterface
{
    public function applyCrop(ImageInterface $image, string $filterName, string $cropCoordinates): void;
}
