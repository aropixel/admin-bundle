<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Crop;

use Aropixel\AdminBundle\Domain\Media\Image\Crop\CropApplierInterface;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class CropApplier implements CropApplierInterface
{
    public function __construct(
        private CacheManager $cacheManager,
        private DataManager $dataManager,
        private FilterManager $filterManager,
    ) {
    }

    private function getRatio(ImageInterface $image): float
    {
        return 600 / $image->getWidth();
    }

    public function applyCrop(ImageInterface $image, string $filterName, string $cropCoordinates): void
    {
        // Liip imagine services
        $dataManager = $this->dataManager;
        $filterManager = $this->filterManager;
        $cacheManager = $this->cacheManager;

        // Get the filter configuration
        $filterConfiguration = $filterManager->getFilterConfiguration()->get($filterName);
        $ratio = $this->getRatio($image);

        // Merge crop configuration with needed coords into the filter configuration
        $coords = explode(',', $cropCoordinates);
        $cropConfiguration = [
            'crop' => [
                'size' => [(float)$coords[2] / $ratio, (float)$coords[3] / $ratio],
                'start' => [(float)$coords[0] / $ratio, (float)$coords[1] / $ratio],
            ],
        ];

        // Merge the "filters" part of the filter configuration
        $mergedFilters = array_merge($cropConfiguration, $filterConfiguration['filters']);

        // Retrieves the image with the given filter applied
        $relativePath = Image::UPLOAD_DIR . '/' . $image->getFilename();
        $binary = $dataManager->find($filterName, $relativePath);

        // Apply the crop
        $filteredBinary = $filterManager->apply($binary, ['filters' => $mergedFilters]);

        // Store & overwrite image
        $cacheManager->store($filteredBinary, $relativePath, $filterName);
    }
}
