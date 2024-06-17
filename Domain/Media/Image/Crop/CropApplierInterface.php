<?php

namespace Aropixel\AdminBundle\Domain\Media\Image\Crop;

interface CropApplierInterface
{
    public function applyCrop(string $fileName, string $filterName, string $cropCoordinates): void;
}
