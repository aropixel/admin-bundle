<?php

namespace Aropixel\AdminBundle\Domain\Media\Image\Crop;

class AvailableCropFilter
{
    // Coordinates to draw (separated by coma), if given
    public ?string $coordinates = null;

    // Image's ratio
    public float $ratio;

    // Liip filter's slug
    public string $slug;

    // Human understandable description of the crop
    public string $description;
}
