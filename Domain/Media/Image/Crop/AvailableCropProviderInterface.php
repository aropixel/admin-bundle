<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 13/02/2023 à 10:55
 */

namespace Aropixel\AdminBundle\Domain\Media\Image\Crop;

use Aropixel\AdminBundle\Entity\CroppableInterface;

interface AvailableCropProviderInterface
{
    public function getAvailableCropFilters(?CroppableInterface $croppable, ?array $configuredFilters=null) : array;
}
