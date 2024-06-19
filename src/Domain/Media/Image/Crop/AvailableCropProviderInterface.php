<?php

namespace Aropixel\AdminBundle\Domain\Media\Image\Crop;

use Aropixel\AdminBundle\Entity\CroppableInterface;

interface AvailableCropProviderInterface
{
    public function getAvailableCropFilters(?CroppableInterface $croppable, ?array $configuredFilters = null): array;
}
