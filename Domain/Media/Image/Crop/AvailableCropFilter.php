<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 13/02/2023 à 14:06
 */

namespace Aropixel\AdminBundle\Domain\Media\Image\Crop;

class AvailableCropFilter
{
    // Coordinates to draw (separated by coma), if given
    public string $coordinates;

    // Image's ratio
    public float $ratio;

    // Liip filter's slug
    public string $slug;

    // Human understandable description of the crop
    public string $description;
}
