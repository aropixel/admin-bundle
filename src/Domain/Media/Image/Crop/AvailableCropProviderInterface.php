<?php

namespace Aropixel\AdminBundle\Domain\Media\Image\Crop;

use Aropixel\AdminBundle\Entity\CroppableInterface;

interface AvailableCropProviderInterface
{
    /**
     * @param array<string,string>|null $configuredFilters
     * @return array<string,AvailableCropFilter>
     */
    public function getAvailableCropFilters(?CroppableInterface $croppable, ?array $configuredFilters = null): array;
}
