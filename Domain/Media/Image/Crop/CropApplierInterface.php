<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 10/02/2023 à 15:31
 */

namespace Aropixel\AdminBundle\Domain\Media\Image\Crop;

interface CropApplierInterface
{
    public function applyCrop(string $fileName, string $filterName, string $cropCoordinates) : void;
}
