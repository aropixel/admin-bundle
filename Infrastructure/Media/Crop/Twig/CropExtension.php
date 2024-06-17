<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Crop\Twig;

use Aropixel\AdminBundle\Domain\Media\Image\Crop\AvailableCropProviderInterface;
use Aropixel\AdminBundle\Entity\CroppableInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CropExtension extends AbstractExtension
{
    public function __construct(
        private readonly AvailableCropProviderInterface $availableCropProvider
    ) {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('get_available_crop_filters', $this->getAvailableCropFilters(...)), new TwigFunction('get_class_available_crop_filters', $this->getClassAvailableCropFilters(...))];
    }

    public function getAvailableCropFilters(?CroppableInterface $croppable, ?array $availableCropList = null): array
    {
        return $this->availableCropProvider->getAvailableCropFilters($croppable, $availableCropList);
    }

    public function getClassAvailableCropFilters(string $className): array
    {
        return $this->availableCropProvider->getAvailableCropFilters(new $className());
    }
}
